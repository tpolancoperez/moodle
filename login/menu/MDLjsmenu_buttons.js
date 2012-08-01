_menuCloseDelay=500           // The time delay for menus to remain visible on mouse out
_menuOpenDelay=150            // The time delay before menus open on mouse over
_subOffsetTop=-1               // Sub menu top offset (was 5)
_subOffsetLeft=6            // Sub menu left offset (was -10)

with(menuStyle=new mm_style()){
itemwidth="189";
subimage="";
onsubimage=""
fontsize="95%";
offcolor="#000000";
offbgcolor="#F9F5EC";
oncolor="#FFFFFF";
onbgcolor="#B18F36";
//onborder="1px solid #000000";
pagecolor="#000000";
pagebgcolor="#F9F5EC";
pageimage="http://www2.oakland.edu/misc/jsmenu/PageImage_RedArrows.gif";
headercolor = "#000000";
headerbgcolor = "#D9C89C";
headerborder="1px solid #000000";
separatorsize="0";
separatorcolor="#F9F5EC";
padding="5";
subimagepadding="0";
fontstyle="bold";
fontfamily="Arial, Verdana, Tahoma";
}

submenuStyle=new copyOf(menuStyle)
submenuStyle.fontsize="100%";
submenuStyle.padding="3";
submenuStyle.itemwidth="200";
submenuStyle.overallwidth="200";
submenuStyle.borderwidth = 1
submenuStyle.separatorcolor="#F9F5EC";
submenuStyle.separatorsize="3";
with(milonic=new menuname("Moodle")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Login to Moodle;url=https://moodle.oakland.edu;status=Login to Moodle;image=;");
aI("text=TECHNICAL SUPPORT;type=header;align=center;status=TECHNICAL SUPPORT;image=;");
aI("text=System Status;url=http://www2.oakland.edu/elis/statusmoodle.cfm;status=System Status;image=;");
aI("text=Request Help;url=http://www2.oakland.edu/elis/help.cfm?LMS=2;status=Request Help;image=;");
aI("text=Moodle Instructions;url=http://www2.oakland.edu/elis/moodlehelp.cfm;status=Moodle Instructions;image=;");
aI("text=Moodle Student Orientation;url=http://www2.oakland.edu/elis/SO_index.cfm;status=Moodle Student Orientation;image=;");
aI("text=FACULTY FORMS;type=header;align=center;status=FACULTY FORMS;image=;");
aI("text=Development Courses;url=http://www2.oakland.edu/elis/develop.cfm;status=Development Courses;image=;");
aI("text=Faculty Test Accounts;url=http://www2.oakland.edu/elis/testaccount.cfm;status=Faculty Test Accounts;image=;");
aI("text=Moodle/WebCT Archives;url=http://www2.oakland.edu/elis/archive.cfm;status=Moodle/WebCT Archives;image=;");
aI("text=Combined Courses;url=http://www2.oakland.edu/elis/combined.cfm;status=Combined Courses;image=;");
aI("text=Course Sharing Permission;url=http://www2.oakland.edu/elis/permission_form.cfm;status=Course Sharing Permission;image=;");
aI("text=Faculty Guest Accounts;url=http://www2.oakland.edu/elis/guestaccount.cfm;status=Faculty Guest Accounts;image=;");
aI("text=Faculty Improvement Suggestions;url=http://www2.oakland.edu/elis/suggestions.cfm;status=Faculty Improvement Suggestions;image=;");
}
with(milonic=new menuname("ePortfolio")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Login to ePortfolio;url=https://portfolio.oakland.edu;status=Login to ePortfolio;image=;");
aI("text=Overview of ePortfolio;url=http://www2.oakland.edu/elis/eportfolio.cfm;status=Overview of ePortfolio;image=;");
aI("text=TECHNICAL SUPPORT;type=header;align=center;status=TECHNICAL SUPPORT;image=;");
aI("text=Request Help;url=http://www2.oakland.edu/elis/help.cfm?LMS=3;status=Request Help;image=;");
aI("text=Request A Space;url=http://www2.oakland.edu/elis/requestspace.cfm;status=Request A Space;image=;");
}
with(milonic=new menuname("Student Orientation")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Welcome;url=http://www2.oakland.edu/elis/SO_index.cfm;status=Welcome;image=;");
aI("text=Online Courses;url=http://www2.oakland.edu/elis/SO_courses.cfm;status=Online Courses;image=;");
aI("text=Technical Requirements;url=http://www2.oakland.edu/elis/SO_techreq.cfm;status=Technical Requirements;image=;");
aI("text=FAQ;url=http://www2.oakland.edu/elis/SO_faq.cfm;status=FAQ;image=;");
aI("text=Login Procedures;url=http://www2.oakland.edu/elis/SO_login.cfm;status=Login Procedures;image=;");
aI("text=Demo Course;url=http://www2.oakland.edu/elis/SO_demo.cfm;status=Demo Course;image=;");
aI("text=Online Self-Assessment Test;url=http://www2.oakland.edu/elis/SO_assessment.cfm;status=Online Self-Assessment Test;image=;");
aI("text=Evaluation;url=http://www2.oakland.edu/elis/SO_eval.cfm;status=Evaluation;image=;");
}
with(milonic=new menuname("WebCT")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Login to WebCT;url=http://webct.oakland.edu;status=Login to WebCT;");
aI("text=TECHNICAL SUPPORT;type=header;align=center;status=TECHNICAL SUPPORT;");
aI("text=Browser Check;url=http://webct.oakland.edu/webct/public/check_browser.pl?back_key=entry_page;target=new;status=Browser Check;");
aI("text=System Status;url=http://www2.oakland.edu/elis/status.cfm;status=System Status;");
aI("text=Request Help;url=http://www2.oakland.edu/elis/help.cfm?LMS=1;status=Request Help;");
aI("text=WebCT Instructions;url=http://www2.oakland.edu/elis/docs_elearn.cfm;status=WebCT Instructions;image=;");
aI("text=WebCT Student Orientation;url=http://www2.oakland.edu/elis/SO_index.cfm;status=WebCT Student Orientation;image=;");
aI("text=FACULTY FORMS;type=header;align=center;status=FACULTY FORMS;");
aI("text=Respondus Request;url=http://www2.oakland.edu/elis/respondusrequest.cfm;status=Respondus Request;image=;");
}
with(milonic=new menuname("Scantron")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Overview of Scantron;url=http://www2.oakland.edu/elis/scantron.cfm;status=Overview of Scantron;image=;");
aI("text=Quizzes and Exams;url=http://www2.oakland.edu/elis/scantron_tests.cfm;status=Quizzes and Exams;image=;");
aI("text=Course Evaluations;url=http://www2.oakland.edu/elis/scantron_evals.cfm;status=Course Evaluations;image=;");
aI("text=Assessments;url=http://www2.oakland.edu/elis/scantron_assess.cfm;status=Assessments;image=;");
aI("text=Course Evaluations Header Sheets Order Form;url=http://www2.oakland.edu/elis/scantron_forms.cfm;status=Course Evaluations Header Sheets Order Form;image=;");
}
with(milonic=new menuname("Elluminate")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Login to Elluminate;url=https://elluminate.oakland.edu;status=Login to Elluminate;image=;");
aI("text=Overview of Elluminate;url=http://www2.oakland.edu/elis/elluminate.cfm;status=Overview of Elluminate;image=;");
aI("text=TECHNICAL SUPPORT;type=header;align=center;status=TECHNICAL SUPPORT;image=;");
aI("text=Request Help;url=http://www2.oakland.edu/elis/elluminatehelp.cfm;status=Request Help;image=;");
aI("text=Elluminate Scheduling Request;url=http://www2.oakland.edu/elis/elluminateschedule.cfm;status=Elluminate Scheduling Request;image=;");
}
with(milonic=new menuname("Second Life")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Overview of Second Life;url=http://www2.oakland.edu/elis/secondlife.cfm;status=Overview of Second Life;image=;");
aI("text=TECHNICAL SUPPORT;type=header;align=center;status=TECHNICAL SUPPORT;image=;");
aI("text=Technical Requirements;url=http://www2.oakland.edu/elis/sltechreq.cfm;status=Technical Requirements;image=;");
aI("text=Request Help;url=http://www2.oakland.edu/elis/slhelp.cfm;status=Request Help;image=;");
}
with(milonic=new menuname("Turnitin")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Login to Turnitin.com;url=http://www.turnitin.com;status=Login to Turnitin.com;image=;");
aI("text=Overview of Turnitin.com;url=http://www2.oakland.edu/elis/turnitin.cfm;status=Overview of Turnitin.com;image=;");
}
with(milonic=new menuname("iClicker")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Introduction;url=http://www2.oakland.edu/elis/srs_intro.cfm;status=Introduction;image=;");
aI("text=Equipment;url=http://www2.oakland.edu/elis/srs_equip.cfm;status=Equipment;image=;");
aI("text=Personal Views;url=http://www2.oakland.edu/elis/srs_views.cfm;status=Personal Views;image=;");
aI("text=FACULTY FORMS;type=header;align=center;status=FACULTY FORMS;image=;");
aI("text=iClicker Roster File Request;url=http://www2.oakland.edu/elis/srs_roster.cfm;status=iClicker Roster File Request;image=;");
}
with(milonic=new menuname("Help Documents")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Moodle & Other Instructions;url=http://www2.oakland.edu/elis/moodlehelp.cfm;status=Moodle & Other Instructions;image=;");
aI("text=Web Development Help;url=http://www2.oakland.edu/elis/docs.cfm;status=Web Development Help;");
aI("text=Content Resources;url=http://library.oakland.edu/information/services/facultyDigital.htm;target=new;status=Content Resources;image=;");
}
with(milonic=new menuname("Pedagogy")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Teaching with Technology;url=http://www2.oakland.edu/elis/ped_teaching.cfm;status=Teaching with Technology;image=;");
aI("text=Online Course Stipends;url=http://www2.oakland.edu/elis/online_course_stipends.cfm;status=Online Course Stipends;image=;");
aI("text=Online Course Components;url=http://www2.oakland.edu/elis/ped_components.cfm;status=Online Course Components;image=;");
aI("text=Online Course Planning;url=http://www2.oakland.edu/elis/ped_planning.cfm;status=Online Course Planning;image=;");
aI("text=Conversion Chart;url=http://www2.oakland.edu/elis/ped_chart.cfm;status=Conversion Chart;image=;");
aI("text=Syllabus Design;url=http://www2.oakland.edu/elis/ped_syllabus.cfm;status=Syllabus Design;image=;");
aI("text=Good Web Page Design;url=http://www2.oakland.edu/elis/ped_webdesign.cfm;status=Good Web Page Design;image=;");
aI("text=Copyright;url=http://www2.oakland.edu/elis/ped_copyright.cfm;status=Copyright;image=;");
aI("text=Links;url=http://www2.oakland.edu/elis/ped_links.cfm;status=Links;image=;");
aI("text=Overview of Courseware Options;url=http://www2.oakland.edu/elis/overview.cfm;status=Overview of Courseware Options;image=;");
aI("text=Publications;url=http://www2.oakland.edu/elis/publications.cfm;status=Publications;image=;");
}
with(milonic=new menuname("Web Development")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Web Application Development;url=http://www2.oakland.edu/elis/appdev.cfm;status=Web Application Development;");
aI("text=Web Site Prototyping and Support;url=http://www2.oakland.edu/elis/proto.cfm;status=Web Site Prototyping and Support;");
}
with(milonic=new menuname("Events")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Upcoming Events;url=http://www2.oakland.edu/elis/events_coming.cfm;status=Upcoming Events;image=;");
aI("text=Past Events;url=http://www2.oakland.edu/elis/events.cfm;status=Past Events;image=;");
}
drawMenus();
