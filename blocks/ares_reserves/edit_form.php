<?php
include_once("ItemDisplayFormats.php");

class block_ares_reserves_edit_form extends block_edit_form {    
	protected function specific_definition($mform) {
        global $CFG, $formatTypes;
        
        // Display modes
        $itemDisplayModes = array("none" => "None", "text" => "Text", "link" => "Link");
        
        // Course modes
        $courseDisplayModes = array("none" => "None", "link" => "Link");
        
        if(empty($this->config->courseSemester))
        {
            //$this->config->courseSemester = "current_semester";
            $this->config->courseSemester = "";
        }
        
        if(isset($CFG->defaultDisplayFormat))
        {
            $defaultDisplayFormat = $CFG->defaultDisplayFormat;
        }
        else
        {
            $defaultDisplayFormat = "";
        }
		
        // Student view config options
        if(empty($this->config->student_itemDisplayMode))
        {
            $this->config->student_itemDisplayMode = "none";
        }
        if(empty($this->config->student_itemDisplayFormat))
        {
            $this->config->student_itemDisplayFormat = $defaultDisplayFormat;
        }
        if(empty($this->config->student_courseDisplayMode))
        {
            $this->config->student_courseDisplayMode = "link";
        }
        
        // Teacher view config options
        if(empty($this->config->teacher_itemDisplayMode))
        {
            $this->config->teacher_itemDisplayMode = "none";
        }
        if(empty($this->config->teacher_itemDisplayFormat))
        {
            $this->config->teacher_itemDisplayFormat = $defaultDisplayFormat;
        }
        if(empty($this->config->teacher_courseDisplayMode))
        {
            $this->config->teacher_courseDisplayMode = "link";
        }
                
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $semesterList = $this->GetSemesters();
        $mform->addElement('select', 'config_courseSemester', get_string('semesterLabel', 'block_ares_reserves'), $semesterList);
		$mform->setDefault('config_courseSemester', $this->config->courseSemester);
        
        // Student item display format
        $mform->addElement('select', 'config_student_itemDisplayFormat', get_string('student_itemDisplayFormatLabel', 'block_ares_reserves'), $formatTypes);
        $mform->setDefault('config_student_itemDisplayFormat', $this->config->student_itemDisplayFormat);
        
        // Student item display mode
        $mform->addElement('select', 'config_student_itemDisplayMode', get_string('student_itemDisplayModeLabel', 'block_ares_reserves'), $itemDisplayModes);
        $mform->setDefault('config_student_itemDisplayMode', $this->config->student_itemDisplayMode);
        
        // Student course display mode
        $mform->addElement('select', 'config_student_courseDisplayMode', get_string('student_courseDisplayModeLabel', 'block_ares_reserves'), $courseDisplayModes);
        $mform->setDefault('config_student_courseDisplayMode', $this->config->student_courseDisplayMode);
        
        // Teacher item display format
        $mform->addElement('select', 'config_teacher_itemDisplayFormat', get_string('teacher_itemDisplayFormatLabel', 'block_ares_reserves'), $formatTypes);
        $mform->setDefault('config_teacher_itemDisplayFormat', $this->config->teacher_itemDisplayFormat);
        
        // Teacher item display mode
        $mform->addElement('select', 'config_teacher_itemDisplayMode', get_string('teacher_itemDisplayModeLabel', 'block_ares_reserves'), $itemDisplayModes);
        $mform->setDefault('config_teacher_itemDisplayMode', $this->config->teacher_itemDisplayMode);
        
        // Teacher course display mode
        $mform->addElement('select', 'config_teacher_courseDisplayMode', get_string('teacher_courseDisplayModeLabel', 'block_ares_reserves'), $courseDisplayModes);
        $mform->setDefault('config_teacher_courseDisplayMode', $this->config->teacher_courseDisplayMode);        
    }
	
	function GetSemesters() {
		global $CFG;
		
        $semesters = array("current_semester" => "Current Semester");
        
		$webServiceLocation = $CFG->block_ares_reserves_serviceLoc."/areswebservice.asmx";
        $userAgent = (isset($CFG->block_ares_reserves_userAgent) ? $CFG->block_ares_reserves_userAgent : "Moodle");

		// Setting location to override WSDL location in order to be able to use a service such as Runscope
        $options = array(
            "user_agent"    => $userAgent,
            "location"      => $webServiceLocation,
            "cache_wsdl"    => WSDL_CACHE_NONE
        );

        $soapClient = new SoapClient($webServiceLocation."?wsdl", $options);
        
		$semesterList = $soapClient->GetSemesters()->GetSemestersResult;
		if(property_exists($semesterList, "MySemester"))
		{
			$semesterList = $semesterList->MySemester;
			if(is_array($semesterList))
			{
				for($i = 0; $i < count($semesterList); $i += 1)
				{
					$addDate = strtotime($semesterList[$i]->ItemAdditionDate);
					$endDate = strtotime($semesterList[$i]->EndDate);
					$currentDate = time(); 
					if($addDate > $currentDate || $endDate < $currentDate)
					{
						continue;
					}
					
                    $semesters[$semesterList[$i]->SemesterName] = $semesterList[$i]->SemesterName;
				}
			}
			else
			{
                $semesters[$semesterList->SemesterName] = $semesterList->SemesterName;
			}
		}
        
        return $semesters;
	}
}
