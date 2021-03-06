<?PHP
namespace language;
use nrns;



class languageProvider extends nrns\Provider {
	
	private $active;
	private $supported=['de'];
	private $translations=[];
	private $started = false;
	
	public function __construct($nrns, $client, $cookie, $fs) {
		$this->client 	= $client;
		$this->cookie 	= $cookie;
		$this->fs		= $fs;
	}
	
	public function getService() {
		if(!$this->started) {
			$this->start();
		}
		return $this;
	}
	
	private function start() {
		
		if( isset( $_GET['language'] ) ) {
			
			$langOffer = $_GET['language'];
			
		} elseif( $lang = $this->getLanguageCookie() ) {
			
			$langOffer = $lang;
			
		} elseif( $lang = $this->client->getLanguage() ) {
			
			$langOffer = $lang;
			
		} else {
			$langOffer = $this->getDefault();
		}
		
		$this->setActive($langOffer);
		$this->started = true;
	}
	
	public function setActive($lang) {
		if( !$this->isSupported($lang) ) {
			$lang = $this->getDefault();
		}
		$this->setLanguageCookie($lang);
		$this->active = $lang;
	}
	
	public function support($languages=[]) {
		if(count($languages) > 0) {
			$this->supported = $languages;
		}
		return $this;
	}
	
	public function isSupported($language) {
		return in_array($language, $this->supported);
	}
	
	public function setLanguageCookie($lang) {
		$this->cookie->set('language', $lang, 3600*24, '/');
	}
	public function getLanguageCookie() {
		return $this->cookie->get('language');
	}
	
	public function getDefault() {
		return $this->supported[0];
	}
	
	public function getActive() {
		return $this->active;
	}
	
	public function __tostring() {
		return (string) $this->getActive();
	}
	
	
	
	
	public function translate($key) {
		
		$lang = $this->getActive();
		
		if( isset($this->translations[$key][$lang]) ) {
			return $this->translations[$key][$lang];
		} else {
			if( isset($this->translations[$key][$this->getDefault()]) ) {
				return $this->translations[$key][$this->getDefault()];
			}else{
				return '[***Translate: '.$key.'***]';
			}
			
		}
		
	}
	
	public function register($key, $translations=[]) {
		$this->translations[$key] = (array) $translations;
		return $this;
	}
	
	public function registerMultiple($translations=[]) {
		foreach($translations as $key => $val) {
			$this->register($key, $val);
		}
		return $this;
	}
	
	public function registerFromJson($file) {
		if( $file = $this->fs->find($file) ) {
			$this->registerMultiple( $file->parseAs('JSON') );
		}
	}

	public function registerFromMysqlTable($table, $pdo) {
		$query = 'SELECT * FROM `'.$table.'`';
		$stmt = $pdo->prepare($query);
		$stmt->execute();
		$data = $stmt->fetchAll(\PDO::FETCH_CLASS);

		foreach($data as $row) {
			$this->register($row->key, $row);
		}

		$this->registerMultiple($data);
	}
	
}


?>