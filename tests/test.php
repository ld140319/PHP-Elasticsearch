<?php
use PhpES\EsClient\Client;

include '../vendor/autoload.php';

testMatch();

function testMatch()
{
	$es = new Client();
	$es->setHost('10.0.0.235', 9200);
	$res = $es
		->from('houses_1', 'house')
		->match('name','农业','phrase','should')
		->match('address','金水','phrase','should')
		->limit(10)
		->debug()
		->search();
	print_r($res->getFormat());
}

function getTop100()
{
	$es = new Client();
	$es->setHost('127.0.0.1', 9202);
	$res = $es
		->from('statistics-2017-03,statistics-2017-04,statistics-2017-05,statistics-2017-06', '')
		->limit(0)
		->groupBy('community_id', '_count', 'desc', 100)
		->getJsonDsl();
	print_r($res);
	die;
	$res = $res->getFormat();
	var_export($res['aggregations']['community_id']['buckets']);
}

function testGeo()
{
	$es = new Client();
	$es->setHost('10.0.0.235', 9200);
	$res = $es
		->from('houses_1', 'house')
		->where('city_id', '=', '4101')
		->where('house_deleted', '=', 0)
		->where('community_deleted', '=', 0)
		->whereGeo('geo_point_gaode', 34.807218, 113.650345, 1000)
		->limit(10)
		->orderByGeo('geo_point_gaode', 34.807218, 113.650345)
		->debug()
		->search();
	print_r($res->getFormat());
}

function testNear()
{
	$es = new Client();
	$es->setHost('10.0.0.235', 9200);
	$res = $es
		->from('rent_1', 'rent')
		->where('city_id', '=', '4101')
		->where('district_id', '=', '14')
		->where('rent_status', '=', 0)
		->orWhereBegin()
		->where('agent_code', '!=', 0)
		->where('contact_type', '=', 1)
		->orWhereEnd()
		->orderByNear('price', 999)
		->limit(10)
		->debug()
		->search();
	print_r($res->getFormat());
}

function testRent()
{
	$script = "tmScore = _score;if(doc['cover'].value != null){tmScore = tmScore+10;}; return tmScore + doc['create_time'];";
	$es     = new Client();
	$es->setHost('10.0.0.235', 9200);
	$res = $es
		->from('rent_1', 'rent')
		->where('city_id', '=', '4101')
		->where('district_id', '=', '14')
		->where('price', 'between', array(2000, 2500))
		->where('area', 'between', array(50, 70))
		->where('rooms', '=', '1')
		->where('decorating_type', '=', '简装')
		->where('rent_status', '=', 0)
		->orWhereBegin()
		->where('agent_code', '!=', 0)
		->where('contact_type', '=', 1)
		->orWhereEnd()
		->orderBy('has_cover', 'desc')
		->orderBy('update_time', 'desc')
		->orderByScript($script, array(), 'desc')
		->limit(10)
		->debug()
		->search();
	print_r($res->getFormat());
}

function testWebHouse()
{
	$es = new Client();
	$es->setHost('10.0.0.235', 9200);
	$res = $es
		->from('houses_1', 'house')
		->where('city_id', '=', '4101')
		->where('district_id', '=', '14')
		->where('price', 'between', array(80, 100))
		->where('area', 'between', array(70, 90))
		->where('rooms', '=', '2')
		->where('decorating_type', '=', '简装')
		->where('house_deleted', '=', 0)
		->where('community_deleted', '=', 0)
		->orWhereBegin()
		->where('deal_time', '=', 0)
		->where('deal_time', '>=', 1494148539)
		->orWhereEnd()
		->orderBy('deal_time')
		->orderBy('recommend_weight', 'desc')
		->orderBy('from_type', 'desc')
		->orderBy('update_time', 'desc')
		->limit(10)
		->debug()
		->search();
	print_r($res->getFormat());
}

function testAggs()
{
	$es = new Client();
	$es->setHost('10.0.0.235', 9200);

	$res = $es
		->select(array('community_id', 'name','address'))
		->from('community_1', 'community')
		->where('soft_deleted', '=', '0')
		->match(array('name'), array('农业'))
		->orderBy('community_id', 'desc')
		->groupBy('subway', '_count', 'desc')
		// ->debug()
		->getJsonDsl();
	print_r($res);
}

function testErp()
{
	$es = new Client();
	$es->setHost('10.0.0.237', 9200);
	$res = $es
		->from('erp-follow-house-2017-05-31', 'n4101')
		->where('house_id', '=', '594243b87f8b9a3a08d2b1a5')
		->where('system', '!=', TRUE)
		->where('types', '!=', 1015)
		->orWhereBegin()
		->andWhereBegin()
		->where('id', '!=', 1)
		->andWhereEnd()
		->where('types', 'not in', array(1009, 1010, 1016, 1017, 1012))
		->orWhereEnd()
		->orWhereBegin()
		->where('admin_id', '=', '55f238add6e4688e648b45d8')
		->where('types', 'in', array(1009, 1010, 1016, 1017, 1012))
		->orWhereEnd()
		->debug()
		->getArrayDsl(TRUE);
	print_r($res);
}

/**
 * response
 * Array
 * (
 * [found] =>
 * [_index] => test
 * [_type] => t
 * [_id] => 3
 * [_version] => 1
 * [_shards] => Array
 * (
 * [total] => 2
 * [successful] => 1
 * [failed] => 0
 * )
 *
 * )
 */
function testDelete()
{
	$index = 'test';
	$type  = 't';
	$id    = 3;

	$es  = new Client();
	$res = $es->setHost('10.0.0.235', 9200)->delete($index, $type, $id)->getArray();
	print_r($res);
}

/**
 * Array
 * (
 * [_index] => test
 * [_type] => t
 * [_id] => 1
 * [_version] => 3  //es记录的数据版本号 可以理解为: 变更历史, 每次变更都将自增
 * [_shards] => Array
 * (
 * [total] => 2
 * [successful] => 1
 * [failed] => 0
 * )
 *
 * )
 */
function testUpdate()
{
	$index = 'test';
	$type  = 't';
	$id    = 1;
	$data  = array(
		'name' => '测试的1更改2',
	);

	$es  = new Client();
	$res = $es->setHost('10.0.0.235', 9200)->update($index, $type, $id, $data)->getArray();
	print_r($res);
}

/**
 * ====================if exists
 * Array
 * (
 * [error] => Array
 * (
 * [root_cause] => Array
 * (
 * [0] => Array
 * (
 * [type] => document_already_exists_exception
 * [reason] => [t][2]: document already exists
 * [shard] => 0
 * [index] => test
 * )
 *
 * )
 *
 * [type] => document_already_exists_exception
 * [reason] => [t][2]: document already exists
 * [shard] => 0
 * [index] => test
 * )
 *
 * [status] => 409
 * )
 * ======================right
 *Array
 * (
 * [_index] => test
 * [_type] => t
 * [_id] => 3
 * [_version] => 1
 * [_shards] => Array
 * (
 * [total] => 2
 * [successful] => 1
 * [failed] => 0
 * )
 *
 * [created] => 1
 * )
 */
function testInsert()
{
	$index = 'test';
	$type  = 't';
	$id    = 8;
	$data  = array(
		'aid'   => 31,
		'bid'   => 32,
		'cid'   => 33,
		'did'   => 34,
		'eid'   => 35,
		'fid'   => 36,
		'id'    => $id,
		'name'  => '测试的1',
		'tid'   => 37,
		'type'  => 31,
		'state' => 31,
	);

	$es  = new Client();
	$res = $es->setHost('10.0.0.235', 9200)->insert($index, $type, $id, $data)->getArray();
	print_r($res);
}

function testMultiFilter()
{
	$es = new Client();
	$es->setHost('10.0.0.235', 9200);

	$res = $es
		// ->select(array('house_id', 'community_id', 'house_deleted', 'user_id', 'geo_point_gaode'))
		->from('houses_1', 'house')
		->where('house_deleted', '=', '0')
		->where('community_deleted', '=', '0')
		->whereGeo('geo_point_gaode', '34.723324', '113.713878', 1, 1, 'm')
		->orWhere('house_id', '!=', 94888)
		->andWhereBegin()
		->orWhere('community_id', '=', 4)
		->orWhere('house_id', '!=', 94887)
		->orWhere('house_id', '!=', 94886)
		->andWhereEnd()
		->orderBy('create_time', 'house_id')
		->offset(0)
		->limit(100)
		// ->debug()
		->search();
	print_r($res);
}