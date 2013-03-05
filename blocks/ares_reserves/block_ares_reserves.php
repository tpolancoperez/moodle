<?php
	class block_ares_reserves extends block_list
	{
		var $soapClient;
		var $config_global;
		var $isTeacher = false;
        
		function init()
        {
			$this->title = 'eReserves';
		}

		function get_content()
        {
            if ($this->content !== NULL)
            {
                return $this->content;
            }
            
			include_once("ItemDisplayFormats.php");
			
			global $CFG, $COURSE, $USER;			
			$this->content = new stdClass;
			$this->content->items = array();
			$this->content->icons = array();
			$this->content->footer = "";
			$roles = get_user_roles(get_context_instance(CONTEXT_COURSE, $COURSE->id), $USER->id);
			
			if($this->config == null)
			{
				$this->LoadStartingConfig();
			}
			foreach($roles as $role)
			{
				if($role->name == "Teacher")
				{
					$this->isTeacher = true;
					break;
				}
			}
			
			$this->soapClient = new SoapClient($CFG->block_ares_reserves_serviceLoc."/areswebservice.asmx?wsdl", array("user_agent" => $CFG->block_ares_reserves_userAgent));
			
			if($this->isTeacher)
			{
				$this->HandleTeacher();
			}			
			else
			{
				$this->HandleStudent();
			}

			return $this->content;			
		}
	
		function applicable_formats()
		{
			return array('course-view' => true);
		}
	
		function instance_allow_config()
		{
			return true;
		}
		
		function has_config() {
			return true;
		}
		
		function config_save($data)
		{
			foreach($data as $name => $value)
			{
				if($name == "block_ares_reserves_serviceLoc" || $name == "block_ares_reserves_siteLoc")
				{
					$value = trim($value, "/");
				}
				set_config($name, $value, 'block/ares_reserves');
			}
		}

		function HandleTeacher()
		{
			global $CFG, $USER, $COURSE;
			
			// Create the user if it doesnt exist 
			if($this->soapClient->UserExists(array("userName" =>$USER->username))->UserExistsResult == false)
			{
				$this->RegisterUser($USER, true);
			}
			
			// Create the course if it doesn't exist
			$course = $this->soapClient->GetCourseByExternalId(array("externalId" => $COURSE->id))->GetCourseByExternalIdResult;
			if($course->ClassId == 0)
			{
				if(empty($this->config->courseSemester))
				{
					$this->content->items[] = "Quarter is not configured.";
					return;
				}
				
				if($this->config->courseSemester == "current_semester")
				{
					$semesterToUse = $this->soapClient->GetCurrentSemester()->GetCurrentSemesterResult->SemesterName;
				}
				else
				{
					$semesterToUse = $this->config->courseSemester;
				}
				
				// The start and end dates are the only values that can be checked to see if this is a valid semester. It is not a pretty method, but it works and the web service doesn't provide a better way.
				if( $this->soapClient->GetSemesterInfo(array("semesterName" => $semesterToUse))->GetSemesterInfoResult->StartDate == "0001-01-01T00:00:00") 
				{
					$this->content->items[] = "Quarter is not configured.";
					return;
				}
				
				$classId = $this->soapClient->NewExternalCourse(array("name" => $COURSE->fullname, "courseNumber" => $COURSE->idnumber, "department" => $USER->department, "semester" => $semesterToUse, "instructorUsername" => $USER->username, "externalCourseId" => $COURSE->id))->NewExternalCourseResult;
				$this->AddUserToCourse($USER->username, $classId, "Instructor");
				$sessionId = $this->GetSessionId($USER->username);
				$aresUrl = $CFG->block_ares_reserves_siteLoc."/ares.dll?Action=10&Form=60&SessionID=".$sessionId."&Value=".$classId;
				if($this->config->teacher_courseDisplayMode == "link")
				{
					$this->content->items[] = "<a target=\"_blank\" href='".$aresUrl."'>".get_string('courseLinkText', 'block_ares_reserves')."</a>";
				}
			}
			else
			{
				$this->AddUserToCourse($USER->username, $course->ClassId, "ProxyInstructor");
				$this->GenerateReserveList($course->ClassId, $USER->username);
			}
		}
		
		function HandleStudent()
		{
			global $USER, $COURSE;

			$course = $this->soapClient->GetCourseByExternalId(array("externalId" => $COURSE->id))->GetCourseByExternalIdResult;
			if($course->ClassId != 0)
			{
				$userExists = $this->soapClient->UserExists(array("userName" => $USER->username))->UserExistsResult;
				if(!$userExists)
				{
					$this->RegisterUser($USER, false);
					$this->AddUserToCourse($USER->username, $course->ClassId, "User");
				}
				else
				{
					$isEnrolled = $this->soapClient->VerifyCourseMembership(array("classId" => $course->ClassId, "userName" => $USER->username))->VerifyCourseMembershipResult;
					if(!$isEnrolled)
					{
						$this->AddUserToCourse($USER->username, $course->ClassId, "User");
					}
				}
				$this->GenerateReserveList($course->ClassId, $USER->username);
			}
			else
			{
				$this->content->items = array();
				return;
			}
		}
		
		function GetSessionId($username)
		{
			return $this->soapClient->CreateWebSession(array("username" => $username))->CreateWebSessionResult;
		}
		
		function GenerateReserveList($classId, $userName)
		{
			global $CFG;
			
			$sessionId = $this->GetSessionId($userName);
			$aresUrl = $CFG->block_ares_reserves_siteLoc."/ares.dll?Action=10&Form=60&SessionID=".$sessionId."&Value=".$classId;
			if(($this->isTeacher && $this->config->teacher_courseDisplayMode == "link") || (!$this->isTeacher && $this->config->student_courseDisplayMode == "link"))
			{
				$this->content->items[] = "<a target=\"_blank\" href='".$aresUrl."'>".get_string('courseLinkText', 'block_ares_reserves')."</a>";
			}
			
			$reserveList = $this->soapClient->ClassItems(array("classID" => $classId, "username" => $userName))->ClassItemsResult;
			if(property_exists($reserveList, "MyItem"))
			{
				$reserveList = $reserveList->MyItem;
			}
			else
			{
				return;
			}
			if(!is_array($reserveList))
			{
				$reserveList = array($reserveList);
			}
			if(count($reserveList) > 0)
			{
				for($i = 0; $i < count($reserveList); $i += 1)
				{
					$itemText = FormatItemText($reserveList[$i], ($this->isTeacher ? ($this->config->teacher_itemDisplayFormat) : ($this->config->student_itemDisplayFormat)));
					$aresUrl = $CFG->block_ares_reserves_siteLoc."/ares.dll?Action=10&Form=50&SessionID=".$sessionId."&Value=".$reserveList[$i]->ItemID;
					if($this->isTeacher)
					{
						$displayMode = $this->config->teacher_itemDisplayMode;
					}
					else
					{
						$displayMode = $this->config->student_itemDisplayMode;
					}
					switch($displayMode)
					{
						case "none":
							break;
						case "text":
							$this->content->items[] = $itemText;
							break;
						case "link":
							$this->content->items[] = "<a class='ares_reserve_item' target=\"_new\" href='".$aresUrl."'>".$itemText."</a>";
							break;
					}
				}
			}
		}
		
		function RegisterUser($user, $isInstructor)
		{
			$this->soapClient->NewExternalUser(array("Username" => $user->username,"FirstName" => $user->firstname,"LastName" => $user->lastname,"LibraryId" => 0,"EmailAddress" => $user->email,"Status" => "","Department" => $user->department,"ExternalUserId" => $user->id));
			if($isInstructor)
			{
				$this->soapClient->InstructorPrivileges(array("Username" => $user->username));
			}
		}
		
		function AddUserToCourse($username, $classId, $userType)
		{
			$this->soapClient->AddUsertoCourse(array("UserName" => $username, "ClassId" => $classId, "UserType" => $userType));
		}
		///////////////////////////////////////////////
		// MODIFIED 7/26/12 - MIKE SEILER & MATT LUMPKIN
		// SETTING THE DEFAULT CONFIGURATION MODES FOR ARES BLOCKS
		// SEE EDIT_FORM.PHP FOR MODIFICATIONS ON FORM ELEMENTS
		//	
		function LoadStartingConfig()
		{
			if(isset($this->config_global->defaultDisplayFormat))
			{
				$defaultDisplayFormat = $this->config_global->defaultDisplayFormat;
			}
			else
			{
				$defaultDisplayFormat = "";
			}
			// We don't want the course semester to be set by default because the system will pick it up and run with it, using the default to create the course before the user ever gets a chance to change it.
			if(isset($this->config->courseSemester))
				unset($this->config->courseSemester);
			// Student view config options
			$this->config->student_itemDisplayMode = "none";
			$this->config->student_itemDisplayFormat = $defaultDisplayFormat;
			$this->config->student_courseDisplayMode = "link";

			//Teach view config options
			$this->config->teacher_itemDisplayMode = "none";
			$this->config->teacher_itemDisplayFormat = $defaultDisplayFormat;
			$this->config->teacher_courseDisplayMode = "link";
		}
	}
?>
