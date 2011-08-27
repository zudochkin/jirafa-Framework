#Sinatra PHP analog
##Features
```
$app->get('/', function() use ($registry) { // показываем главную
  					return '<br>1hi ';
					})
	->get('404', function($registry) { // отображается, если ни один маршрут не подошел
						var_dump($registry);
						return '<h1>Error 404</h1>';
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
```

Or something like that.
