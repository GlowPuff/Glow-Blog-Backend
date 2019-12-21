
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

## Things To Enhance
Since I'm using a complete JWT solution, I'd like to ween off of using Firebase authentication and have my system create and sign its own tokens.  There is already code in place to create a single user in a User database object.

### Prerequisites
PHP 5.6

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Acknowledgments
* [SleekDB](https://sleekdb.github.io/) A flat file database storage system
* [UwAmp](https://www.uwamp.com/) Portable server environment
* [php-jwt](https://github.com/firebase/php-jwt) Encode/decode JSON Web Tokens (JWT) in php