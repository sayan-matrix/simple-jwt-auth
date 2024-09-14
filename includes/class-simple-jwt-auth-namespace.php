<?php

/**
 * Define a `wrapper namespace` to load the library classes
 * and prevent conflicts with other plugins using the same library
 * with different versions.
 *
 * @since      1.0.1
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth/includes
 * @author     Sayan Dey <mr.sayandey18@outlook.com>
 */

namespace Simple_Jwt_Auth\Firebase\JWT;

class JWT extends \Firebase\JWT\JWT {

}
 
class Key extends \Firebase\JWT\Key {

}