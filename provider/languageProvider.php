<?PHP
namespace language;
use nrns;



class languageProvider extends nrns\Provider {
	
	private $active;
	private $supported=['de'];
	private $translations=[];
	
	
	public function __construct($nrns, $client, $cookie, $fs) {
		$this->client 	= $client;
		$this->cookie 	= $cookie;
		$this->fs		= $fs;
		
		$nrns->on('run', function(){
		
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
			
		});
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
		$this->cookie->set('language', $lang, 3600*24);
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
		return $this->getActive();
	}
	
	
	
	public function translate($key) {
		
		$lang = $this->getActive();
		
		if( isset($this->translations[$key][$lang]) ) {
			return $this->translations[$key][$lang];
		} else {
			if( isset($this->translations[$key][$this->getDefault()]) ) {
				return $this->translations[$key][$this->getDefault()];
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
	
}


?>