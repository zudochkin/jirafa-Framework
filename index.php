<?php

class Jirafa 
{
    protected $_app = array();
	protected $_registry;
	
	public function get() {
		$args = func_get_args();
        $this->_app[array_shift($args)] = array_shift($args);
		
        return $this;
    }    
    
    public function render() {	
		$uri = ($_SERVER['REQUEST_URI'] === '/') ? '/' : substr($_SERVER['REQUEST_URI'], 1);
		// iterate over all paths
		foreach($this->_app as $path => $func) {
			// if $uri and regular expression are matched
			if (preg_match('@^' . $path . '\/*$@', $uri, $matches)) {
				var_dump($path);
				array_shift($matches);
				$countInPath = count($matches);
				
				$reflection = new ReflectionFunction($func);
				$params = $reflection->getParameters();
				
				$array = array();
				foreach($params as $param) 
					$array[$param->getName()] = array_shift($matches);
				
				//if (count($array) !== $countInPath + 1)
					//goto error;
				
				echo call_user_func_array($func, array_merge($array, array('registry' => $this->_registry)));
				return;
			} 
		}
		//error:
			echo call_user_func_array($this->_app['404'], array('registry' => $this->_registry));
    }
	
	public function setRegistry($registry) {
		$this->_registry = $registry;
	}
}

$app = new Jirafa();

$app->setRegistry(array(
	'viewObject' => 'may be smarty',
	'dbObject' => new PDO('sqlite:./db/db.db') //'may be pdo or something else'
));

$app->get('/', function() use ($registry) { // mainpage
						return '<br>1hi ';
					})
	->get('404', function($registry) { // nothing found
						var_dump($registry);
						return '<h1>Error 404</h1>';
					})
    ->get('(\d+)', function($digits) use ($registry) { // example.com/222323
						return '<br>2test' . $digits;
					})
    ->get('(\w+)\/(\w+)', function($category, $product, $registry) { // example.com/section/article
						echo '<hr>';
						var_dump($registry);
						var_dump($category);
						var_dump($product);
					})
	->get('(\w+).html', function ($alias, $registry) { // example.com/static.html
						$pdo = $registry['dbObject'];
						$html = 'Error';
						try {
							$row = $pdo->query("SELECT * FROM content WHERE alias='{$alias}'")->fetch();
							$html = "<h1>{$row['title']}</h1><p>{$row['content']}</p>";
						} catch (PDOException $e) {
							return $e->getMessage();
						}
						return $html;
					})
	->get('about', function($registry) { // example.com/about
						$html = '<h1>About</h1>';
						$html .= '<a href="http://vredniy.ru">Vredniy</a>';
						return $html;
					})
;
$app->render();
