<?php

declare(strict_types=1);

namespace MySqlMcp\Tests\Unit;

use MySqlMcp\Services\SecurityService;
use MySqlMcp\Exceptions\SecurityException;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

/**
 * Test des permissions DDL (CREATE, ALTER, DROP)
 * Reproduit et vérifie la correction du bug #3 de GitHub
 */
class DDLPermissionsTest extends Unit
{
    protected UnitTester $tester;

    /**
     * Test que les opérations DDL sont bloquées par défaut
     */
    public function testDDLOperationsBlockedByDefault()
    {
        $config = [
            'ALLOW_DDL_OPERATIONS' => 'false',
            'ALLOW_ALL_OPERATIONS' => 'false',
            'BLOCK_DANGEROUS_KEYWORDS' => 'true'
        ];
        
        $securityService = new SecurityService($config);

        // CREATE devrait être bloqué
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Mot-clé non autorisé détecté: CREATE');
        $securityService->validateQuery("CREATE TABLE test (id INT)", 'CREATE');
    }

    /**
     * Test que ALTER est bloqué par défaut
     */
    public function testAlterOperationBlockedByDefault()
    {
        $config = [
            'ALLOW_DDL_OPERATIONS' => 'false',
            'ALLOW_ALL_OPERATIONS' => 'false'
        ];
        
        $securityService = new SecurityService($config);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Mot-clé non autorisé détecté: ALTER');
        $securityService->validateQuery("ALTER TABLE test ADD COLUMN name VARCHAR(50)", 'ALTER');
    }

    /**
     * Test que DROP est bloqué par défaut
     */
    public function testDropOperationBlockedByDefault()
    {
        $config = [
            'ALLOW_DDL_OPERATIONS' => 'false'
        ];
        
        $securityService = new SecurityService($config);

        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Mot-clé non autorisé détecté: DROP');
        $securityService->validateQuery("DROP TABLE test", 'DROP');
    }

    /**
     * Test principal : les opérations DDL sont autorisées quand ALLOW_DDL_OPERATIONS=true
     * Reproduit la correction du bug #3
     */
    public function testDDLOperationsAllowedWhenConfigured()
    {
        $config = [
            'ALLOW_DDL_OPERATIONS' => 'true',
            'ALLOW_ALL_OPERATIONS' => 'false',
            'BLOCK_DANGEROUS_KEYWORDS' => 'true'
        ];
        
        $securityService = new SecurityService($config);

        // Ces opérations ne devraient plus lever d'exception
        $securityService->validateQuery("CREATE TABLE test (id INT)", 'CREATE');
        $securityService->validateQuery("ALTER TABLE test ADD COLUMN name VARCHAR(50)", 'ALTER');
        $securityService->validateQuery("DROP TABLE test", 'DROP');
        
        // Si nous arrivons ici, le test a réussi
        $this->assertTrue(true);
    }

    /**
     * Test que ALLOW_ALL_OPERATIONS autorise tout y compris DDL
     */
    public function testAllOperationsMode()
    {
        $config = [
            'ALLOW_DDL_OPERATIONS' => 'false', // Même si DDL est false
            'ALLOW_ALL_OPERATIONS' => 'true',   // ALL prime sur DDL
            'BLOCK_DANGEROUS_KEYWORDS' => 'true'
        ];
        
        $securityService = new SecurityService($config);

        // DDL devrait être autorisé grâce à ALLOW_ALL
        $securityService->validateQuery("CREATE TABLE test (id INT)", 'CREATE');
        $securityService->validateQuery("ALTER TABLE test ADD COLUMN name VARCHAR(50)", 'ALTER');
        $securityService->validateQuery("DROP TABLE test", 'DROP');
        
        $this->assertTrue(true);
    }

    /**
     * Test que ALLOW_ALL_OPERATIONS autorise même les mots-clés dangereux
     */
    public function testAllOperationsModeAllowsDangerousKeywords()
    {
        $config = [
            'ALLOW_ALL_OPERATIONS' => 'true',
            'BLOCK_DANGEROUS_KEYWORDS' => 'true' // Même avec blocage activé
        ];
        
        $securityService = new SecurityService($config);

        // GRANT devrait être autorisé car ALLOW_ALL=true prime sur BLOCK_DANGEROUS
        $securityService->validateQuery("GRANT ALL ON *.* TO 'user'@'%'", 'UNKNOWN');
        
        $this->assertTrue(true);
    }

    /**
     * Test que les mots-clés dangereux restent bloqués si ALLOW_ALL=false
     */
    public function testDangerousKeywordsStillBlockedWhenNotAllowAll()
    {
        $config = [
            'ALLOW_DDL_OPERATIONS' => 'true',   // DDL autorisé
            'ALLOW_ALL_OPERATIONS' => 'false',  // Mais pas tout
            'BLOCK_DANGEROUS_KEYWORDS' => 'true'
        ];
        
        $securityService = new SecurityService($config);

        // GRANT devrait rester bloqué
        $this->expectException(SecurityException::class);
        $this->expectExceptionMessage('Mot-clé non autorisé détecté: GRANT');
        $securityService->validateQuery("GRANT ALL ON *.* TO 'user'@'%'", 'UNKNOWN');
    }

    /**
     * Test que les mots-clés DDL dans des contextes complexes fonctionnent
     */
    public function testComplexDDLQueries()
    {
        $config = ['ALLOW_DDL_OPERATIONS' => 'true'];
        $securityService = new SecurityService($config);

        // Requêtes DDL complexes
        $complexQueries = [
            "CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
            "ALTER TABLE users ADD INDEX idx_created (created_at), ADD FOREIGN KEY (user_id) REFERENCES users(id)",
            "DROP INDEX idx_created ON users"
        ];

        foreach ($complexQueries as $query) {
            $securityService->validateQuery($query, 'CREATE'); // L'opération importe peu ici
        }
        
        $this->assertTrue(true);
    }

    /**
     * Test que les faux positifs sont évités (ex: created_at ne déclenche pas CREATE)
     */
    public function testWordBoundariesPreventFalsePositives()
    {
        $config = ['ALLOW_DDL_OPERATIONS' => 'false'];
        $securityService = new SecurityService($config);

        // Ces requêtes ne devraient pas être bloquées car elles contiennent des mots
        // qui incluent les mots-clés DDL mais ne sont pas des mots-clés complets
        $securityService->validateQuery("SELECT created_at, altered_by FROM users", 'SELECT');
        $securityService->validateQuery("SELECT * FROM users WHERE name = 'CreateUser'", 'SELECT');
        
        $this->assertTrue(true);
    }
}