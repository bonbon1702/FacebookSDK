FacebookSDK
===========

  Installation
------------
 * In composer.json, add require:
 ```
  "bonbon1702/facebooksdk": "dev-master"
 ```
 * Run
 ```
 Composer update
 ```

 * In app/config/app.php:
 
    -Add providers
  ```
  'bonbon1702\Facebook\FbServiceProvider',
  ```
    -Add aliases
  ```
  'Fb' => 'bonbon1702\Facebook\Facades\Fb',
  ```
  
  
* Publish the configuration:
  ```
  php artisan config:publish bonbon1702/facebooksdk
  ```
* After that, you must config app_id and app_secret in 
```
app/config/packages/bonbon1702/facebooksdk/config.php
```
```php
return array(
	'app_id'		=>	'',
	'app_secret'	=>	'',
	'redirect_url'	=>	url('facebook/callback'),
	'scope'			=>  array(
		'publish_actions',
	)
);
```
  
  
Usage
------------
-Log in to Facebook
```
Fb::authenticate();
```
-Check login(return boolean)
```
Fb::check()
```
-Get user profile
```
Fb::getProfile()->asArray();
```
-Get user profile picture
```
Fb::getUserProfilePicture($type)->asArray();($type is square,small,normal,large);
```
-Publish posts to facebook 
```
Fb::postToTimeLine($message, $link);
```



Example
------------
```php
Route::group(['prefix' => 'facebook'], function ()
{
	Route::get('connect', function ()
	{
		return Fb::authenticate();
	});

	Route::get('callback', function ()
	{
		$check = Fb::check();

		if($check)
		{
			$profile = Fb::getProfile();
			
			dd($profile);
		}

	});
});
```
