<?php
include 'pagename.php';
    class Session{
        public static function init(){
            ini_set( 'session.cookie_httponly', 1 );
            session_start();
            $currentCookieParams = session_get_cookie_params();
            $sidvalue = session_id();
            setcookie(
                'PHPSESSID',//name
                $sidvalue,//value
                0,//expires at end of session
                $currentCookieParams['path'],//path
                $currentCookieParams['domain'],//domain
                true, //secure
				true  // httponly
            );
            // setcookie('samesite-test', '1', 0, '/; samesite=strict');
        }
        public static function set($key,$val){
            $_SESSION[$key] = $val;
        }
        public static function get($key){
            if(isset($_SESSION[$key])){

                return $_SESSION[$key];
            }else{
                return false;
            }
        }
        public static function checkSession(){
          global $login_page;
            self::init();
            if(self::get("login") == false){
                self::destroy();
                header("location:".$login_page);
            }
        }
        public static function checkSession_f(){
          global $login_page;
            self::init();
            if(self::get("login") == false){
                self::destroy();
                header("location:../app/".$login_page);
            }
        }

        public static function checkSession_d(){
          global $login_page;
            self::init();
            if(self::get("login") == false){
                self::destroy();
                header("location:app/".$login_page);
            }
        }
        public static function Check_auth(){
          global $login_page;
            if(self::get("auth_log") == false){
                self::destroy();
                header("location:".$login_page);
            }
        }
        public static function checkSession_log(){
          global $index_page;
            self::init();
            if(self::get("login") == true){
                header("location:../".$index_page);
            }
        }

        public static function destroy(){
          global $login_page;
            session_destroy();
            header("location:".$login_page);
        }
        public static function destroy_d(){
            global $login_page;
            session_destroy();
            header("location: app/".$login_page);
        }
    }
?>
