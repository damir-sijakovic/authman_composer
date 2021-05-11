<?php

namespace Authman\System\Cookie;

interface CookieInterface
{
	public function set($key, $value);
	public function get($key); 
	public function delete($key); 
	public function keyExists($key); 
	public function deleteAll(); 
}
