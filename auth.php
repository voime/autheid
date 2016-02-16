<?php
/**
 * DokuWiki Plugin eid (Auth Component)
 *
 * @author  Arvi Võime <avoime@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class auth_plugin_autheid extends auth_plugin_authplain {


    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(); // for compatibility

        $this->cando['addUser']     = true; // can Users be created?
        $this->cando['delUser']     = true; // can Users be deleted?
        $this->cando['modLogin']    = true; // can login names be changed?
        $this->cando['modPass']     = true; // can passwords be changed?
        $this->cando['modName']     = true; // can real names be changed?
        $this->cando['modMail']     = true; // can emails be changed?
        $this->cando['modGroups']   = true; // can groups be changed?
        $this->cando['getUsers']    = true; // can a (filtered) list of users be retrieved?
        $this->cando['getUserCount']= true; // can the number of users be retrieved?
        $this->cando['getGroups']   = true; // can a list of available groups be retrieved?
        $this->cando['logout']      = true; // can the user logout?
        $this->cando['external']    = false; // does the module do external auth checking?

        $this->success = true;
    }


    /**
     * Check user+password
     *
     * May be ommited if trustExternal is used.
     *
     * @param   string $user the user name
     * @param   string $pass the clear text password
     * @return  bool
     */
    public function checkPass(&$username, &$password) {

	    session_start();

        // if already logged in
	    if(isset($_SESSION['eid_userdata']) && $_SESSION['eid_userdata']['username']==$username && md5($_SESSION['eid_userdata']['pass'])==$password){
		    return true;
	    }

	    // ID CARD VALIDATION
	    // if($username=='eid'){
    	// 	if(isset($_SESSION['SSL_CLIENT_CERT']) && $_SESSION['SSL_CLIENT_CERT']){
        //
    	// 		// TODO
        //         // vaja kätte saada isikukood
        //
    	// 		if($serialNumber){
    	// 			$userdata = $this->findUserBySerialnumber($serialNumber);
    	// 			if(!$userdata){
    	// 				msg('Did not find user with isikukood: '.$serialNumber, -1);
    	// 			}
    	// 		}
    	// 	}else{
    	// 		msg('ID card was not found', -1);
    	// 		return false;
    	// 	}
	    // }

        // id login

        if ($username == 'eid' and $password == 'eid' and isset($_POST["hash"])){
            $calculated_hash=$_POST["SN"].$_POST["GN"].$_POST["serialNumber"].$_POST["timestamp"].$this->getConf('secret');
            if ($_POST["hash"]==hash("sha256",$calculated_hash)) {
                $userdata = $this->findUserBySerialnumber($_POST["serialNumber"]);
            }else{
                msg('Wrong hash');
            }
        }

        // USERNAME AND PASSWORD VALIDATION
        if(!$userdata && $username && $password && $username!='eid' && $this->getConf('username_login')!=0){
        //if($username && $password && $username!='smartcard'){
          // find user by username and password
          $userdata  = $this->findUserByUsernameAndPassword($username, $password);
        }
        //msg(json_encode($userdata));
	    // LOG IN
	    if($userdata){
            $username = $userdata['username'];
		    $password = md5($userdata['pass']);
		    $_SESSION['eid_userdata'] = $userdata;
		    session_write_close();
		    return true;
	    }

	    // logon failed
	    unset($_SESSION['eid_userdata']);
	    session_write_close();
	    return false;
    }

    /**
     * Finds user by SerialNumber (isikukood)
     *
     */
    public function findUserBySerialNumber($username){

        // TODO validate username (11 int length)

        $userdata = $this->getUserData($username);
        if ($userdata){
	        $userdata['username'] = $username;
            msg('You have logged in with ID card');
        }
        return $userdata;

    }

    /**
     * Finds user by username and password
     *
     */
    public function findUserByUsernameAndPassword($username, $password){
	    $username = preg_replace('/[^\w\d\.-_]/', '', $username);
	    $password = preg_replace('/[^\w\d\.-_]/', '', $password);

	    $userdata = $this->getUserData($username);
        if ($userdata){
	        $userdata['username'] = $username;
            msg('You have logged in with username and password');
        }
        return $userdata;
    }

    /**
     * Return user info
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email addres of the user
     * grps array   list of groups the user is in
     *
     * @param   string $user the user name
     * @return  array containing user data or false
     */
    public function getUserData($user) {
        return parent::getUserData($user);
    }

    /**
     * Create a new User [implement only where required/possible]
     *
     * Returns false if the user already exists, null when an error
     * occurred and true if everything went well.
     *
     * The new user HAS TO be added to the default group by this
     * function!
     *
     * Set addUser capability when implemented
     *
     * @param  string     $user
     * @param  string     $pass
     * @param  string     $name
     * @param  string     $mail
     * @param  null|array $grps
     * @return bool|null
     */
    public function createUser($user, $pass, $name, $mail, $grps = null) {
	    return parent::createUser($user, $pass, $name, $mail, $grps);
    }

    /**
     * Modify user data [implement only where required/possible]
     *
     * Set the mod* capabilities according to the implemented features
     *
     * @param   string $user    nick of the user to be changed
     * @param   array  $changes array of field/value pairs to be changed (password will be clear text)
     * @return  bool
     */
    public function modifyUser($user, $changes) {
	    return parent::modifyUser($user, $changes);
    }

    /**
     * Delete one or more users [implement only where required/possible]
     *
     * Set delUser capability when implemented
     *
     * @param   array  $users
     * @return  int    number of users deleted
     */
    public function deleteUsers($users) {
	    return parent::deleteUsers($users);
    }

    /**
     * Return a count of the number of user which meet $filter criteria
     * [should be implemented whenever retrieveUsers is implemented]
     *
     * Set getUserCount capability when implemented
     *
     * @param  array $filter array of field/pattern pairs, empty array for no filter
     * @return int
     */
    public function getUserCount($filter = array()) {
	    return parent::getUserCount($filter);
    }

    /**
     * Bulk retrieval of user data
     *
     * @param   int   $start index of first user to be returned
     * @param   int   $limit max number of users to be returned
     * @param   array $filter array of field/pattern pairs
     * @return  array userinfo (refer getUserData for internal userinfo details)
     */
    public function retrieveUsers($start, $limit, $filter) {
	    return parent::retrieveUsers($start, $limit, $filter);
    }

    /**
     * Define a group [implement only where required/possible]
     *
     * Set addGroup capability when implemented
     *
     * @param   string $group
     * @return  bool
     */
    public function addGroup($group) {
	    return parent::addGroup();
    }

    /**
     * Retrieve groups [implement only where required/possible]
     *
     * Set getGroups capability when implemented
     *
     * @param   int $start
     * @param   int $limit
     * @return  array
     */
    public function retrieveGroups($start = 0, $limit = 0) {
	    return parent::retrieveGroups();
    }

    /**
     * Return case sensitivity of the backend
     *
     * When your backend is caseinsensitive (eg. you can login with USER and
     * user) then you need to overwrite this method and return false
     *
     * @return bool
     */
    public function isCaseSensitive() {
	    return parent::isCaseSensitive();
    }
}

// vim:ts=4:sw=4:et:
