<?php // Library for connecting to iTunesU from Moodle

   require_once 'HTTP/Request2.php';

   function generate_token($username, $userid, $courseabbr, $courseid, $coursenumber) {
      $debugsuffix = '/pfh879';
      $quarterdesignation = '0208';
      $sharedsecret = 'NDJSLWE6EMTX7GFZXUMZF8J527MYURWE';
      $siteurl = 'https://deimos.apple.com/WebObjects/Core.woa/BrowsePrivately/fuller.edu';
      $administratorcredential = 'Administrator\@urn:mace:itunesu.com:sites:fuller.edu';
      $instructorcredential = 'Instructor\@urn:mace:fuller.edu:classes:';
      #$studentcredential = 'Student\@urn:mace:fuller.edu:classes:'.$courseabbr;
      $studentcredential = 'Student\@urn:mace:fuller.edu:classes:'.$coursenumber;

      $credentialarray = array();
      $token = array();

      //
      // First Generate the Credential String
      //
 
      // If one of these users, we are admins on the iTunesU site

      // See - http://moodle.org/mod/forum/discuss.php?d=153685
      $admins = array('jharwell','starmountain','tpolanco','thomaslister','sds','bjbell','osborn');
      $is_an_admin = has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));
      if (!$is_an_admin) {
          $is_an_admin = in_array($admins, $username);
      }

      // See - http://moodle.org/mod/forum/discuss.php?d=169604
      // and http://docs.moodle.org/dev/Roles#Teachers
      $is_a_teacher = has_capability('moodle/course:manageactivities', get_context_instance(CONTEXT_COURSE, $courseid));
      if ($is_an_admin) {
         array_push($credentialarray, $administratorcredential);
      }
      // If the user in a teacher they get the instructor credentials
      if ($is_a_teacher || $is_an_admin) {
         array_push($credentialarray, $instructorcredential.$coursenumber);
      }
      // And since we are logged in though a course we get the student credenticals for that coursea
      array_push($credentialarray, $studentcredential);

      //
      // Now put together the rest of the necessary information and make the token
      //

      $identity = get_identity_string();  
      $credentialsstring = get_credentials_string($credentialarray);
      $currenttime = time();
      $token = get_authorization_token($identity, $credentialsstring, $currenttime, $sharedsecret);

      return $token;
   }

   function post_token($token) {
      $siteurl = 'https://deimos.apple.com/WebObjects/Core.woa/BrowsePrivately/fuller.edu';
      $debugsuffix = '/pfh879';
      ## Uncomment the following to enable debugging
      ##$siteurl .= $debugsuffix;      
      $r = new HTTP_Request2($siteurl, HTTP_Request2::METHOD_POST, array("timeout" => 20, "ssl_verify_peer" => 0, "ssl_verify_host" => 0));
      $r->addPostParameter($token);

      // If after 5 tries ITunesU has not accepted our request return this 
      // error message.
      $custom_error_body = <<<EOT
<html>
<head><title>Error with ITunesU</title><head>
<body bgcolor=white>
<H2>ITunesU did not respond to our request</H2>
When we tried to connect to ITunesU the service did not respond to our request.  
This may be due to technical difficulties or possibly there are just a large number 
of people trying to use the service at the same time.<br><br>
Please wait a few minutes and try again.<br>
<br>
We apologize for the inconvenience
</body>
</html>
EOT;

      // Try 5 times
      $tries = 0;
      while ($tries < 5) {
         try {
            $response = $r->send();
            $message = $response->getStatus();
            return $response->getBody();
         } catch (HttpException $ex) {
            #print "Returned an Error<br>\n";
            #return $ex->__toString();
            $tries++;
         }
      }
      ## We only get here if we have failed 5 times and fall out of the while loop
      return $custom_error_body;
   }

   function get_authorization_token($identity, $credentials, $currenttime, $sharedsecret) {
      $token = array();
      $signature = '';
      $buffer = '';

      // create the POST Content and sign it
      $buffer .= 'credentials=' . urlencode($credentials);
      $buffer .= '&identity=' . urlencode($identity);
      $buffer .= '&time=' . urlencode($currenttime);
      $signature = hash_hmac('sha256', $buffer, $sharedsecret);

      $token['credentials'] = $credentials;
      $token['identity'] = $identity;
      $token['time'] = $currenttime;
      $token['signature'] = $signature;
      return $token;
   }

   function get_identity_string() {
      // We are using the Moodle environment to pull this information
      // If you are not using moodle you will need to re-implement this
      global $USER;
      $identitystring = '';
      $displayname = '';

      if (!$USER->lastname || !$USER->email || !$USER->username || !$USER->id) {
         print "Not enough user information to generate the identity string, aborting\n";
         exit;
      }

      if ($USER->firstname) {
         $displayname = $USER->firstname." ".$USER->lastname;
      } else {
         $displayname = $USER->lastname;
      }

      // Do the required formating
      return sprintf('"%s" <%s> (%s) [%s]', $displayname, $USER->email, $USER->username, $USER->id);
   }
      

   function get_credentials_string($carray) {
      if (!count($carray)) {
         print "No credentials were assigned, this is an error, exiting.\n";
         exit;
      }
      return implode(';', $carray);
   }
