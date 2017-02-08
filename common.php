﻿<?php

if (!defined('_BR_'))		//*.ini file access, http://develstudio.ru/php-orion/articles/rabotaem-s-fajlami-ini-v-php
	define('_BR_',chr(13).chr(10));
class TIniFileEx {
    public $filename;
    public $arr;
    function __construct($file = false){
        if ($file)
            $this->loadFromFile($file);
    }
    function initArray(){
        $this->arr = parse_ini_file($this->filename, true);
    }
    function loadFromFile($file){
        $result = true;
        $this->filename = $file;
        if (file_exists($file) && is_readable($file)){
            $this->initArray();
        }
        else
            $result = false;
        return $result;
    }
    function read($section, $key, $def = ''){
        if (isset($this->arr[$section][$key])){
            return $this->arr[$section][$key];
        } else
            return $def;
    }
    function write($section, $key, $value){
        if (is_bool($value))
            $value = $value ? 1 : 0;
        $this->arr[$section][$key] = $value;
		if(true)return true;
		else return false;
    }
    function eraseSection($section){
        if (isset($this->arr[$section]))
            unset($this->arr[$section]);
    }
    function deleteKey($section, $key){
        if (isset($this->arr[$section][$key]))
            unset($this->arr[$section][$key]);
    }
    function readSections(&$array){
        $array = array_keys($this->arr);
        return $array;
    }
    function readKeys($section, &$array){
        if (isset($this->arr[$section])){
            $array = array_keys($this->arr[$section]);
            return $array;
        }
        return array();
    }
    function updateFile(){
        $result = '';
        foreach ($this->arr as $sname=>$section){
            $result .= '[' . $sname . ']' . _BR_;
            foreach ($section as $key=>$value){
                $result .= $key .'="'.str_replace('\\', '\\\\', $value) .'"'._BR_;
            }
            $result .= _BR_;
        }
		Log::append ( file_put_contents ( $this->filename, $result )
			? 'Настройки успешно сохранены.'
			: 'Ошибка при сохранении настроек.'
		);
    }
}

function write_config($filename, $cfg, $subsections, $tcs){

	parse_str($cfg);
	$ini = new TIniFileEx($filename);
	
	// т.-клиенты
	if(is_array($tcs)){
		$q = 0;
		foreach($tcs as $cm => $tc){
			$q++;
			if(isset($tc['cl'])	&& $tc['cl'] != '') $ini->write("torrent-client-$q",'client',$tc['cl']);
			if(isset($tc['ht'])	&& $tc['ht'] != '') $ini->write("torrent-client-$q",'hostname',$tc['ht']);
			if(isset($tc['pt'])	&& $tc['pt'] != '') $ini->write("torrent-client-$q",'port',$tc['pt']);
			if(isset($tc['lg'])) $ini->write("torrent-client-$q",'login',$tc['lg']);
			if(isset($tc['pw'])) $ini->write("torrent-client-$q",'password',$tc['pw']);
			if(isset($tc['cm'])	&& $tc['cm'] != '')	$ini->write("torrent-client-$q",'comment',$tc['cm']);
		}
		$ini->write('other', 'qt', $q); // кол-во т.-клиентов
	}
	
	// статусы раздач
	if(isset($topics_status)) $ini->write('sections','topics_status',implode(',', $topics_status));
	
	// регулировка раздач
	if( is_numeric( $peers ) ) $ini->write( 'topics_control', 'peers', $peers );
	$ini->write( 'topics_control', 'leechers', isset( $leechers ) ? 1 : 0 );
	$ini->write( 'topics_control', 'no_leechers', isset( $no_leechers ) ? 1 : 0 );
	
	// прокси
	$ini->write('proxy', 'activate', isset($proxy_activate) ? 1 : 0);
	if(isset($proxy_type)) $ini->write('proxy','type',$proxy_type);
	if(isset($proxy_hostname)) $ini->write('proxy','hostname',$proxy_hostname);
	if(isset($proxy_port)) $ini->write('proxy','port',$proxy_port);
	if(isset($proxy_login)) $ini->write('proxy','login',$proxy_login);
	if(isset($proxy_paswd)) $ini->write('proxy','password',$proxy_paswd);
	
	// подразделы
	if(is_array($subsections)){
		foreach($subsections as $subsec){
			if(isset($subsec['na']) && $subsec['na'] != '') $ini->write($subsec['id'],'title',$subsec['na']);
			if(isset($subsec['cl'])) $ini->write($subsec['id'],'client',!empty($subsec['cl']) ? $subsec['cl'] : '');
			if(isset($subsec['lb'])) $ini->write($subsec['id'],'label',$subsec['lb']);
			if(isset($subsec['fd'])) $ini->write($subsec['id'],'data-folder',$subsec['fd']);
			if(isset($subsec['ln'])) $ini->write($subsec['id'],'link',$subsec['ln']);
		}
		$ini->write('sections','subsections', implode(',', array_column_common($subsections, 'id')));	
	}
	
	// кураторы
	if(isset($dir_torrents)) $ini->write('curators','dir_torrents',$dir_torrents);
	if(isset($passkey)) $ini->write('curators','user_passkey',$passkey);
	
	if(isset($TT_login) && $TT_login != '') $ini->write('torrent-tracker','login',$TT_login);
	if(isset($TT_password) && $TT_password != '') $ini->write('torrent-tracker','password',$TT_password);
	if(isset($bt_key) && $bt_key != '') $ini->write('torrent-tracker','bt_key',$bt_key);
	if(isset($api_key) && $api_key != '') $ini->write('torrent-tracker','api_key',$api_key);
	if(isset($api_url) && $api_url != '') $ini->write('torrent-tracker','api_url',$api_url);
	if(isset($forum_url) && $forum_url != '') $ini->write('torrent-tracker','forum_url',$forum_url);
	if(isset($TT_rule_topics) && $TT_rule_topics != '') $ini->write('sections','rule_topics',$TT_rule_topics);
	if(isset($TT_rule_reports) && $TT_rule_reports != '') $ini->write('sections','rule_reports',$TT_rule_reports);
	if(isset($avg_seeders_period) && $avg_seeders_period != '') $ini->write('sections','avg_seeders_period',$avg_seeders_period);
	if(isset($savedir)) $ini->write('download','savedir',$savedir);
	$ini->write('download','savesubdir',isset($savesubdir) ? 1 : 0);
	$ini->write('sections', 'avg_seeders',isset($avg_seeders) ? 1 : 0);
	$ini->write('download','retracker',isset($retracker) ? 1 : 0);
	
	echo $ini->updateFile(); // обновление файла с настройками
}

function get_settings( $filename = 'config.ini' ){
	
	$config = array();
	
	$ini = new TIniFileEx( dirname(__FILE__) . '/' . $filename );
	
	// торрент-клиенты
	$qt = $ini->read('other','qt','0');
	for($i = 1; $i <= $qt; $i++){
		$comment = $ini->read("torrent-client-$i","comment","");
		$config['clients'][$comment]['cm'] = $comment;
		$config['clients'][$comment]['cl'] = $ini->read("torrent-client-$i","client","");
		$config['clients'][$comment]['ht'] = $ini->read("torrent-client-$i","hostname","");
		$config['clients'][$comment]['pt'] = $ini->read("torrent-client-$i","port","");
		$config['clients'][$comment]['lg'] = $ini->read("torrent-client-$i","login","");
		$config['clients'][$comment]['pw'] = $ini->read("torrent-client-$i","password","");
	}
	if ( isset( $config['clients'] ) && is_array( $config['clients'] ) ) {
		uksort($config['clients'], function($a, $b) {
			return strnatcmp($a, $b);
		});
	}
	
	// подразделы
	$config['subsec'] = $ini->read('sections','subsections','');
	if(!empty($config['subsec'])) $subsections = explode(',', $config['subsec']);
	if(isset($subsections)){
		foreach($subsections as $id){
			$config['subsections'][$id]['id'] = $id;
			$config['subsections'][$id]['na'] = $ini->read("$id","title","");
			$config['subsections'][$id]['cl'] = $ini->read("$id","client","utorrent");
			$config['subsections'][$id]['lb'] = $ini->read("$id","label","");
			$config['subsections'][$id]['df'] = $ini->read("$id","data-folder","");
			$config['subsections'][$id]['ln'] = $ini->read("$id","link","");
		}
		uasort($config['subsections'], function($a, $b){
			$a['title'] = mb_substr($a['na'], mb_strrpos($a['na'], ' » ') + 3);
			$b['title'] = mb_substr($b['na'], mb_strrpos($b['na'], ' » ') + 3);
			return strnatcmp($a['na'], $b['na']);
		});
	}
	
	// раздачи
	$config['rule_topics'] = $ini->read('sections','rule_topics',3);
	$config['rule_reports'] = $ini->read('sections','rule_reports',10);
	$config['avg_seeders'] = $ini->read('sections','avg_seeders',0);
	$config['avg_seeders_period'] = $ini->read('sections','avg_seeders_period',14);
	$config['topics_status'] = explode(',', $ini->read('sections','topics_status','2,8'));
	
	// регулировка раздач
	$config['topics_control']['peers'] = $ini->read( 'topics_control', 'peers', 10 );
	$config['topics_control']['leechers'] = $ini->read( 'topics_control', 'leechers', 0 );
	$config['topics_control']['no_leechers'] = $ini->read( 'topics_control', 'no_leechers', 1 );
	
	// прокси
	$config['proxy_activate'] = $ini->read('proxy','activate',0);
	$config['proxy_type'] = $ini->read('proxy','type','http');
	$config['proxy_hostname'] = $ini->read('proxy','hostname','195.82.146.100');
	$config['proxy_port'] = $ini->read('proxy','port',3128);
	$config['proxy_login'] = $ini->read('proxy','login','');
	$config['proxy_paswd'] = $ini->read('proxy','password','');
	$config['proxy_address'] = $config['proxy_hostname'] . ':' . $config['proxy_port'];
	$config['proxy_auth'] = $config['proxy_login'] . ':' . $config['proxy_paswd'];
	
	// авторизация
	$config['tracker_login'] = $ini->read('torrent-tracker','login','');
	$config['tracker_paswd'] = $ini->read('torrent-tracker','password','');
	$config['bt_key'] = $ini->read('torrent-tracker','bt_key','');
	$config['api_key'] = $ini->read('torrent-tracker','api_key','');
	$config['api_url'] = $ini->read('torrent-tracker','api_url','http://api.rutracker.cc');
	$config['forum_url'] = $ini->read('torrent-tracker','forum_url','http://rutracker.cr');
	
	// загрузки
	$config['save_dir'] = $ini->read('download','savedir','C:\Temp\\');
	$config['savesub_dir'] = $ini->read('download','savesubdir',0);
	$config['retracker'] = $ini->read('download','retracker',0);
	
	// кураторы
	$config['dir_torrents'] = $ini->read('curators','dir_torrents','C:\Temp\\');
	$config['user_passkey'] = $ini->read('curators','user_passkey','');
	
	return $config;
	
}

function convert_bytes($size) {
    $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	$i = $size >= pow(1024,4) ? 3 : floor(log($size, 1024));
	return $size ? round($size / pow(1024, $i), 2) . $filesizename[$i] : '0';
}

function rmdir_recursive($dir, $basedir = false) {
    foreach(scandir($dir) as $file) {
        if ('.' === $file || '..' === $file) continue;
        if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
        else unlink("$dir/$file");
    }
    $basedir ? rmdir($dir) : false;
}

function array_column_common(array $input, $columnKey, $indexKey = null) {
	$array = array();
	foreach ($input as $value) {
		if ( ! isset($value[$columnKey])) {
			trigger_error("Key \"$columnKey\" does not exist in array");
			return false;
		}
		if (is_null($indexKey)) {
			$array[] = $value[$columnKey];
		}
		else {
			if ( ! isset($value[$indexKey])) {
				trigger_error("Key \"$indexKey\" does not exist in array");
				return false;
			}
			if ( ! is_scalar($value[$indexKey])) {
				trigger_error("Key \"$indexKey\" does not contain scalar value");
				return false;
			}
			$array[$value[$indexKey]] = $value[$columnKey];
		}
	}
	return $array;
}

// получить текущую дату
function get_now_datetime() {
	return date('d.m.Y H:i:s') . ' ';
}

// ведение общего лога
class Log {
	
	private static $log;
	
	public static function append ( $message = "" ) {
		if ( !empty ( $message ) )
			self::$log[] = date('d.m.Y H:i:s') . ' ' . $message;
	}
	
	public static function get ( $break = '<br />' ) {
		if ( !empty ( self::$log ) )
			return implode ( $break, self::$log ) . $break;
	}
	
	public static function write ( $filelog ) {
		$dir = dirname(__FILE__) . "/logs";
		$result = is_writable( $dir ) || mkdir( $dir );
		if( !$result )
			echo "Нет или недостаточно прав для доступа к каталогу logs";
		$filelog = "$dir/$filelog";
		self::move ( $filelog );
		if ( $filelog = fopen ( $filelog, "a" ) ) {
			fwrite ( $filelog, self::get ( "\n" ) );
			fwrite ( $filelog, " -- DONE --\n" );
			fclose ( $filelog );
		} else {
			echo "Не удалось создать файл лога.";
		}
	}
	
	private static function move ( $filelog ) {
		// переименовываем файл лога, если он больше 5 Мб
		if ( file_exists($filelog) && filesize($filelog) >= 5242880 ) {
			if ( !rename ( $filelog, preg_replace ( '|.log$|', '.1.log', $filelog ) ) )
				echo "Не удалось переименовать файл лога.";
		}
	}
	
	public static function clean () {
		self::$log = array();
	}
	
}

// установка параметров прокси
class Proxy {
	
	public static $proxy;
	
	protected static $auth;
	protected static $type;
	protected static $address;
	
	private static $types = array( 'http' => 0, 'socks4' => 4, 'socks4a' => 6, 'socks5' => 5 );
	
	public static function options ( $active = false, $type = "http", $address = "", $auth = "" ) {
		self::$type = (array_key_exists($type, self::$types) ? self::$types[$type] : null );
		self::$address = (in_array(null, explode(':', $address)) ? null : $address);
		self::$auth = (in_array(null, explode(':', $auth)) ? null : $auth);
		self::$proxy = $active ? self::set_proxy() : array();
		Log::append ( $active
			? 'Используется ' . mb_strtoupper ( $type ) . '-прокси: "' . $address . '".'
			: 'Прокси-сервер не используется.'
		);
	}
	
	private static function set_proxy () {
		return array(
			CURLOPT_PROXYTYPE => self::$type,
			CURLOPT_PROXY => self::$address,
			CURLOPT_PROXYUSERPWD => self::$auth
		);
	}
	
}

class Db {
	
	public static $db;
	
	public static function query_database($sql, $param = array(), $fetch = false, $pdo = PDO::FETCH_ASSOC){
		self::$db->sqliteCreateFunction('like', 'Db::lexa_ci_utf8_like', 2);
		$sth = self::$db->prepare($sql);
		if(self::$db->errorCode() != '0000') {
			$error = self::$db->errorInfo();
			throw new Exception( 'SQL ошибка: ' . $error[2] );
		}
		$sth->execute($param);
		return $fetch ? $sth->fetchAll($pdo) : true;
	}
	
	// https://blog.amartynov.ru/php-sqlite-case-insensitive-like-utf8/
	public static function lexa_ci_utf8_like ( $mask, $value ) {
	    $mask = str_replace(
	        array("%", "_"),
	        array(".*?", "."),
	        preg_quote($mask, "/")
	    );
	    $mask = "/^$mask$/ui";
	    return preg_match ( $mask, $value );
	}
	
	public static function combine_set( $set ) {
		foreach( $set as $id => &$value ) {
			$value = array_map ( function ($e) {
				return is_numeric($e) ? $e : Db::$db->quote($e);
			}, $value);
			$value = ( empty( $value['id'] ) ? "$id," : "" ) . implode ( ',', $value );
		}
		$statement = 'SELECT ' . implode (' UNION ALL SELECT ', $set);
		return $statement;
	}
	
	public static function create() {
		
		self::$db = new PDO('sqlite:' . dirname(__FILE__) . '/webtlo.db');
		
		// таблицы
		
		// список подразделов
		self::query_database('CREATE TABLE IF NOT EXISTS Forums (
				id INT NOT NULL PRIMARY KEY,
				na VARCHAR NOT NULL
		)');
		
		// разное
		self::query_database('CREATE TABLE IF NOT EXISTS Other AS SELECT 0 AS "id", 0 AS "ud"');
		
		// топики
		self::query_database('CREATE TABLE IF NOT EXISTS Topics (
				id INT NOT NULL PRIMARY KEY,
				ss INT NOT NULL,
				na VARCHAR NOT NULL,
				hs VARCHAR NOT NULL,
				se INT NOT NULL,
				si INT NOT NULL,
				st INT NOT NULL,
				rg INT NOT NULL,
				dl INT NOT NULL DEFAULT 0
		)');
		
		// средние сиды
		self::query_database('CREATE TABLE IF NOT EXISTS Seeders (
			id INT NOT NULL PRIMARY KEY,
			d0 INT, d1 INT,d2 INT,d3 INT,d4 INT,d5 INT,d6 INT,
			d7 INT,d8 INT,d9 INT,d10 INT,d11 INT,d12 INT,d13 INT,
			d14 INT,d15 INT,d16 INT,d17 INT,d18 INT,d19 INT,
			d20 INT,d21 INT,d22 INT,d23 INT,d24 INT,d25 INT,
			d26 INT,d27 INT,d28 INT,d29 INT,
			q0 INT, q1 INT,q2 INT,q3 INT,q4 INT,q5 INT,q6 INT,
			q7 INT,q8 INT,q9 INT,q10 INT,q11 INT,q12 INT,q13 INT,
			q14 INT,q15 INT,q16 INT,q17 INT,q18 INT,q19 INT,
			q20 INT,q21 INT,q22 INT,q23 INT,q24 INT,q25 INT,
			q26 INT,q27 INT,q28 INT,q29 INT
		)');
		
		// хранители
		self::query_database('CREATE TABLE IF NOT EXISTS Keepers (
			id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
			topic_id INTEGER NOT NULL, nick VARCHAR NOT NULL
		)');
		
		// триггеры
		
		// запретить дубликаты в keepers
		self::query_database('CREATE TRIGGER IF NOT EXISTS Keepers_not_duplicate
			BEFORE INSERT ON Keepers
	        WHEN EXISTS (SELECT id FROM Keepers WHERE topic_id = NEW.topic_id AND nick = NEW.nick)
			BEGIN
			    SELECT RAISE(IGNORE);
			END;
		');
		
		// обновить при вставке такой же записи
		self::query_database('CREATE TRIGGER IF NOT EXISTS Forums_update
			BEFORE INSERT ON Forums
	        WHEN EXISTS (SELECT id FROM Forums WHERE id = NEW.id)
			BEGIN
			    UPDATE Forums SET na = NEW.na
			    WHERE id = NEW.id;
			    SELECT RAISE(IGNORE);
			END;
		');
		
		// совместимость со старыми версиями базы данных
		$version = self::query_database('PRAGMA user_version', array(), true);
		
		if($version[0]['user_version'] < 1){
			self::query_database('ALTER TABLE Topics ADD COLUMN rt INT DEFAULT 1');
			self::query_database('ALTER TABLE Topics ADD COLUMN ds INT DEFAULT 0');
			self::query_database('ALTER TABLE Topics ADD COLUMN cl VARCHAR');
			self::query_database('PRAGMA user_version = 1');
		}
		
		if($version[0]['user_version'] < 2) {
			self::query_database('DROP TRIGGER IF EXISTS Seeders_update');
			self::query_database('ALTER TABLE Other ADD COLUMN se INT DEFAULT 0');
			self::query_database('ALTER TABLE Forums ADD COLUMN qt INT');
			self::query_database('ALTER TABLE Forums ADD COLUMN si INT');
			self::query_database('ALTER TABLE Topics RENAME TO TopicsTemp');
			
			self::query_database('CREATE TABLE IF NOT EXISTS Topics (
			    id INT PRIMARY KEY NOT NULL,
			    ss INT,
			    na VARCHAR,
			    hs VARCHAR,
			    se INT,
			    si INT,
			    st INT,
			    rg INT,
			    dl INT,
			    qt INT,
			    ds INT,
			    cl VARCHAR
			)');
			
			self::query_database('INSERT INTO Topics (id,ss,na,hs,se,si,st,rg,dl,qt,ds,cl)
				SELECT id,ss,na,hs,se,si,st,rg,dl,rt,ds,cl FROM TopicsTemp'
			);
			
			self::query_database('DROP TABLE TopicsTemp');
			
			self::query_database('CREATE TRIGGER IF NOT EXISTS delete_seeders
				AFTER DELETE ON Topics FOR EACH ROW
				BEGIN
					DELETE FROM Seeders WHERE id = OLD.id;
				END;'
			);
			
			self::query_database('CREATE TRIGGER IF NOT EXISTS insert_seeders
				AFTER INSERT ON Topics
				BEGIN
					INSERT INTO Seeders (id) VALUES (NEW.id);
				END;
			');
			
			self::query_database('CREATE TRIGGER IF NOT EXISTS topic_exists
				BEFORE INSERT ON Topics
				WHEN EXISTS (SELECT id FROM Topics WHERE id = NEW.id)
				BEGIN
					UPDATE Topics SET
					    ss = CASE WHEN NEW.ss IS NULL THEN ss ELSE NEW.ss END,
					    na = CASE WHEN NEW.na IS NULL THEN na ELSE NEW.na END,
					    hs = CASE WHEN NEW.hs IS NULL THEN hs ELSE NEW.hs END,
					    se = CASE WHEN NEW.se IS NULL THEN se ELSE NEW.se END,
					    si = CASE WHEN NEW.si IS NULL THEN si ELSE NEW.si END,
					    st = CASE WHEN NEW.st IS NULL THEN st ELSE NEW.st END,
					    rg = CASE WHEN NEW.rg IS NULL THEN rg ELSE NEW.rg END,
					    dl = CASE WHEN NEW.dl IS NULL THEN dl ELSE NEW.dl END,
					    qt = CASE WHEN NEW.qt IS NULL THEN qt ELSE NEW.qt END,
					    ds = CASE WHEN NEW.ds IS NULL THEN ds ELSE NEW.ds END,
					    cl = CASE WHEN NEW.cl IS NULL THEN cl ELSE NEW.cl END
					WHERE id = NEW.id;
					SELECT RAISE(IGNORE);
				END;'
			);
			
			self::query_database('CREATE TRIGGER IF NOT EXISTS transfer_seeders
				AFTER UPDATE ON Topics
				WHEN NEW.ds <> OLD.ds
				BEGIN
					UPDATE Seeders SET
						d0 = OLD.se, d1 = d0, d2 = d1, d3 = d2, d4 = d3,
						d5 = d4, d6 = d5, d7 = d6, d8 = d7, d9 = d8,
						d10 = d9, d11 = d10, d12 = d11, d13 = d12, d14 = d13,
						d15 = d14, d16 = d15, d17 = d16, d18 = d17, d19 = d18,
						d20 = d19, d21 = d20, d22 = d21, d23 = d22, d24 = d23,
						d25 = d24, d26 = d25, d27 = d26, d28 = d27, d29 = d28,
						q0 = OLD.qt, q1 = q0, q2 = q1, q3 = q2, q4 = q3,
						q5 = q4, q6 = q5, q7 = q6, q8 = q7, q9 = q8,
						q10 = q9, q11 = q10, q12 = q11, q13 = q12, q14 = q13,
						q15 = q14, q16 = q15, q17 = q16, q18 = q17, q19 = q18,
						q20 = q19, q21 = q20, q22 = q21, q23 = q22, q24 = q23,
						q25 = q24, q26 = q25, q27 = q26, q28 = q27, q29 = q28
					WHERE id = NEW.id;
				END;'
			);
			
			self::query_database('PRAGMA user_version = 2');
		}
	}
	
}

?>
