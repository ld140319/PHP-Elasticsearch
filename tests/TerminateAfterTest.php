<?php
/**
 * @Author Jea
 * test Env: mac php7.1.6
 * phpunit.phar: 6.2.2
 * command: php /www/phar/phpunit.phar --configuration phpunit.xml TerminateAfterTest.php
 */

use PhpES\EsClient\Client;
use PhpES\EsClient\DSLBuilder;
use \PHPUnit\Framework\TestCase;

class TerminateAfterTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testTerminateAfter()
    {
        $es = new Client();
        $es->setHost($_ENV['ES_TEST_HOST'], $_ENV['ES_TEST_PORT']);
        $res = $es
            ->from('index', 'type')
            ->where('projectid', DSLBuilder::OPERATOR_EQ, 44)
            ->orderBy('_id', DSLBuilder::SORT_DIRECTION_DESC)
            ->terminate(3)
            ->limit(10)
            ->debug()
            ->getJsonDsl();
        $dsl = '{"query":{"bool":{"filter":{"bool":{"must":[{"bool":{"must":[{"term":{"projectid":44}}]}}]}}}},"sort":[{"_id":{"order":"desc"}}],"terminate_after":3}';
        $this->assertEquals($dsl, $res);
    }

    /**
     * @throws Exception
     * @throws \PhpES\EsClient\ESORMException
     */
    public function testMultiMatch()
    {
        $es = new Client();
        $es->setHost($_ENV['ES_TEST_HOST'], $_ENV['ES_TEST_PORT']);
        $res = $es
            ->from('index', 'type')
            ->match(['name', 'company_name'], 'keywords', DSLBuilder::MATCH_TYPE_BEST_FIELDS, DSLBuilder::MUST)
            ->orderBy('_id', DSLBuilder::SORT_DIRECTION_DESC)
            ->terminate(3)
            ->limit(10)
            ->debug()
            ->getJsonDsl();
        $dsl = '{"query":{"bool":{"filter":{"bool":{"must":[{"bool":{"must":[{"bool":{"must":{"multi_match":{"query":"keywords","type":"best_fields","fields":["name","company_name"]}}}}]}}]}}}},"sort":[{"_id":{"order":"desc"}}],"terminate_after":3}';
        $this->assertEquals($dsl,$res);
    }
}