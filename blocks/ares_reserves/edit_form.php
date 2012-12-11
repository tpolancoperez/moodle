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
            $this->config->courseSemester = "current_semester";
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
            $this->config->student_itemDisplayMode = "link";
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
            $this->config->teacher_itemDisplayMode = "link";
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
        ///////////////////////////////////////////////////
	// MODIFIED 7/26/2012
	// COMMENTING OUT ADDELEMENT REMOVES ITEMS FROM FORM
	// UNCOMMENT IF YOU WISH TO ADD ELEMENTS BACK TO ERESERVES FORM
	// MODIFIED BY MIKE SEILER & MATT LUMPKIN
	// SET THE DEFAULT VALUES IN BLOCK_ARES_RESERVES.PHP FUNCTION LoadStartingConfig() 
        // Student item display format
        //$mform->addElement('select', 'config_student_itemDisplayFormat', get_string('student_itemDisplayFormatLabel', 'block_ares_reserves'), $formatTypes);
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
		
        $semesters = array("current_semester" => "Current Quarter");
        
		if(isset($CFG->block_ares_reserves_userAgent))
		{
			$userAgent = $CFG->block_ares_reserves_userAgent;
		}
		else
		{
			$userAgent = "Moodle";
		}
		
        ini_set("soap.wsdl_cache_enabled", "0");
        $soapClient = new SoapClient($CFG->block_ares_reserves_serviceLoc."/areswebservice.asmx?wsdl", array("user_agent" => $userAgent));
        
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
