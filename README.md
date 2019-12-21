
# Glow Blog Backend

The php back-end for my [Glow Blog system](https://github.com/GlowPuff/Glow-Blog), using Firebase JSON Web Tokens for security.  I built this for my GlowBlog Dashboard front-end, but it could be used on its own and integrated with your own front-end.  The JavaScript API is included in this project (BlogAPI folder) to use for that purpose.



## Features

* Flat file system with no reliance on SQL databases
* CRUD database operations
* Pagination for reading posts
* Post Tag filtering
* Uses Firebase JSON Web Tokens to enforce security
* Automatically caches Firebase security keys for faster responses
* Handles image uploading and thumbnail creation
* Includes an easy-to-use JavaScript API for use in your own projects

### NOTES
Use the core.php file to setup your Firebase project ID, which is required for token verification (property $fbProjectId).  This file also has a property called $approvedOrigin (defaults to 'https://localhost:8080' for development) to allow OPTIONS HTTP requests to go through.  Change this to your actual server for production.

**UwAmp and secure cURL requests:** The backend makes a secure connection to a Google server in order to grab the latest Firebase keys. In order to make the secure connection, UwAmp has to be configured properly.
[Original Instructions](https://mrant.net/uwamp-curl-error-60-ssl-certificate-problem-unable-to-get-local-issuer-certificate-see-http-curl-haxx-se-libcurl-c-libcurl-errors-html/)
```
UwAmp cURL error 60: SSL certificate problem: unable to get local issuer certificate (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)

If you get the above error you may need to specify a certificate file in your PHP.INI which you can download from  [https://curl.haxx.se/ca/cacert.pem](https://curl.haxx.se/ca/cacert.pem)

Once downloaded edit your PHP.INI and set the following

[curl]
; A default value for the CURLOPT_CAINFO option.
; This is required to be an absolute path.
curl.cainfo = "C:\uwamp\bin\php\cacert.pem"
```
I put my cacert.pem file right in C:\

### Prerequisites
PHP 5.6

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Acknowledgments
* [SleekDB](https://sleekdb.github.io/) A flat file database storage system
* [UwAmp](https://www.uwamp.com/) Portable server environment
* [php-jwt](https://github.com/firebase/php-jwt) Encode/decode JSON Web Tokens (JWT) in php