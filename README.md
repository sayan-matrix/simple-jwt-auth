=== Simple JWT Auth ===
Contributors: sayandey18
Donate link: https://github.com/sayandey18
Tags: json web token, jwt auth, jwt, rest api, authentication
Requires at least: 5.2 or higher
Tested up to: 6.6.2
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Extends the WP REST API using JSON Web Tokens for robust authentication, providing a secure and reliable way to access and manage WordPress data.

== Description ==

Extends the WordPress REST API using JSON Web Tokens for robust authentication and authorization. 

JSON Web Token (JWT) is an open standard ([RFC 7519](https://tools.ietf.org/html/rfc7519)) that defines a compact and self-contained way for securely transmitting information between two parties.

It provides a secure and reliable way to access and manage WordPress data from external applications, making it ideal for building headless CMS solutions.

- Support & question: [WordPress support forum](#)
- Reporting plugin's bug: [GitHub issues tracker](https://github.com/sayandey18/simple-jwt-auth/issues)

**Plugins GitHub Repo** https://github.com/sayandey18/simple-jwt-auth

## Enable PHP HTTP Authorization Header

#### Shared Hosts

Most shared hosts have disabled the **HTTP Authorization Header** by default.

To enable this option you'll need to edit your **.htaccess** file by adding the following:

`
RewriteEngine on
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
`

#### WPEngine

To enable this option you'll need to edit your .htaccess file adding the follow:

`
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
`

## Configuration

#### Configurate the Signing Key

Simple JWT Auth plugin needs a **Signing Key** to encrypt and decrypt the **secret key**, **private key**, and **public key**. This signing key must be exact 32 charecter long and never be revealed.

To add the **signing key** edit your `wp-config.php` file and add a new constant called **SIMPLE_JWT_AUTH_ENCRYPT_KEY**

`
define( 'SIMPLE_JWT_AUTH_ENCRYPT_KEY', 'your-32-char-signing-key' );
`

## REST Endpoints

When the plugin is activated, a new namespace is added.

`
/wp-jwt/v1
`

Also, two new endpoints are added to this namespace.

| Endpoints                         | HTTP Verb |
|-----------------------------------|:---------:|
| /wp-json/wp-jwt/v1/token          |    POST   |
| /wp-json/wp-jwt/v1/token/validate |    POST   |

### Requesting/Generating Token

To generate a new token, submit a POST request to this endpoint. With `username` and `password` as the parameters.

It will validates the user credentials, and returns success response including a token if the authentication is correct or returns an error response if the authentication is failed.

`
curl --location 'https://example.com/wp-json/wp-jwt/v1/token' \
--header 'Content-Type: application/json' \
--data-raw '{
    "username": "wordpress_username",
    "password": "wordpress_password"
}'
`

#### Sample of success response

`
{
    "code": "simplejwt_auth_credential",
    "message": "Token created successfully",
    "data": {
        "status": 200,
        "id": "2",
        "email": "sayandey@outlook.com",
        "nicename": "sayan_dey",
        "display_name": "Sayan Dey",
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3dwLnNlcnZlcmhvbWUuYml6IiwiaWF0IjoxNzI3NTU0MzYwLCJuYmYiOjE3Mjc1NTQzNjAsImV4cCI6MTcyODE1OTE2MCwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMiJ9fX0.9cOvUrCXNYW3v2IyyYOZ3omc0MxMFFagzP3BTFsAkr0"
    }
}
`

#### Sample of error response

`
{
    "code": "simplejwt_invalid_username",
    "message": "Error: The username admin_user is not registered on this site. If you are unsure of your username, try your email address instead.",
    "data": {
        "status": 403
    }
}
`

Once you get the token, you can store it somewhere in your application:

- using **Cookie** 
- or using **localstorage** 
- or using a wrapper like [localForage](https://localforage.github.io/localForage/) or [PouchDB](https://pouchdb.com/)
- or using local database like SQLite
- or your choice based on app you develop

Then you should pass this token as _Bearer Authentication_ header to every API call.

`
Authorization: Bearer your-generated-token
`

Here is an example to create WordPress post using JWT token authentication.

`
curl --location 'https://example.com/wp-json/wp/v2/posts' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL3dwLnNlcnZlcmhvbWUuYml6IiwiaWF0IjoxNzI3NTU0MzYwLCJuYmYiOjE3Mjc1NTQzNjAsImV4cCI6MTcyODE1OTE2MCwiZGF0YSI6eyJ1c2VyIjp7ImlkIjoiMiJ9fX0.9cOvUrCXNYW3v2IyyYOZ3omc0MxMFFagzP3BTFsAkr0' \
--data '{
    "title": "Dummy post through API",
    "content": "Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
    "status": "publish",
    "tags": [
        4,
        5,
        6
    ]
}'
`

Plugin's middleware intercepts every request to the server, checking for the presence of the **Authorization** header. If the header is found, it attempts to decode the JWT token contained within.

Upon successful decoding, the middleware extracts the user information stored in the token and authenticates the user accordingly, ensuring that only authorized requests are processed.

== Installation ==

This section describes how to install the plugin and get it working.

= Using FTP Client =

1. Download the latest plugin from [here](https://github.com/sayandey18/simple-jwt-auth)
2. Unzip the `simple-jwt-auth.zip` file in your computer.
3. Upload `simple-jwt-auth` folder into the `/wp-content/plugins/` directory.
4. Activate the plugin through the 'Plugins' dashboard.

= Uploading from Dashboard =

1. Download the latest plugin from [here](https://github.com/sayandey18/simple-jwt-auth)
2. Navigate to the Plugins section and click 'Add New Plugin' from the dashboard.
3. Navigate to the Upload area by clicking on the 'Upload Plugin' button.
4. Select the `simple-jwt-auth.zip` from your computer.
5. Click on the 'Install Now' button.
6. Activate the plugin through the 'Plugins' dashboard.

== Frequently Asked Questions ==

= A question that someone might have =

An answer to that question.

= What about foo bar? =

Answer to foo bar dilemma.

== Screenshots ==



== Changelog ==

= 1.0.0 =
* Initial Release.

== Upgrade Notice ==
.