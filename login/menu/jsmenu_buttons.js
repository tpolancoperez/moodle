_menuCloseDelay=500           // The time delay for menus to remain visible on mouse out
_menuOpenDelay=150            // The time delay before menus open on mouse over
_subOffsetTop=-1               // Sub menu top offset (was 5)
_subOffsetLeft=6            // Sub menu left offset (was -10)

with(menuStyle=new mm_style()){
itemwidth="189";
subimage="";
onsubimage=""
fontsize="85%";
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
submenuStyle.padding="3";
submenuStyle.itemwidth="200";
submenuStyle.overallwidth="200";
submenuStyle.borderwidth = 1
submenuStyle.separatorcolor="#F9F5EC";
submenuStyle.separatorsize="3";
with(milonic=new menuname("e-LIS")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Home;url=http://www2.oakland.edu/elis/index.cfm;status=Home;");
aI("text=Staff;url=http://www2.oakland.edu/elis/staff.cfm;status=Staff;");
aI("text=Online Course Stipends;url=http://www2.oakland.edu/elis/online_course_stipends.cfm;status=Online Course Stipends;image=;");
}
with(milonic=new menuname("e-Learning")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Login to WebCT;url=http://webct.oakland.edu;status=Login to WebCT;");
aI("text=TECHNICAL SUPPORT;type=header;align=center;status=TECHNICAL SUPPORT;");
aI("text=Browser Check;url=http://webct.oakland.edu/webct/public/check_browser.pl?back_key=entry_page;target=new;status=Browser Check;");
aI("text=System Status;url=http://www2.oakland.edu/elis/status.cfm;status=System Status;");
aI("text=Request Help;url=http://www2.oakland.edu/elis/help.cfm;status=Request Help;");
aI("text=WebCT Instructions;url=http://www2.oakland.edu/elis/docs_elearn.cfm;status=WebCT Instructions;image=;");
aI("text=WebCT Student Orientation;url=http://www2.oakland.edu/elis/WSO_index.cfm;status=WebCT Student Orientation;image=;");
aI("text=FACULTY FORMS;type=header;align=center;status=FACULTY FORMS;");
aI("text=WebCT Development Courses;url=http://www2.oakland.edu/elis/develop.cfm;status=WebCT Development Courses;");
aI("text=Faculty Test Accounts;url=http://www2.oakland.edu/elis/testaccount.cfm;status=Faculty Test Accounts;");
aI("text=Cross-list (Merge) Courses;url=http://www2.oakland.edu/elis/crosslist.cfm;status=Cross-list (Merge) Courses;image=;");
aI("text=Archives;url=http://www2.oakland.edu/elis/archive.cfm;status=Archives;");
aI("text=Respondus Request;url=http://www2.oakland.edu/elis/respondusrequest.cfm;status=Respondus Request;image=;");
aI("text=Course Sharing Permission;url=http://www2.oakland.edu/elis/permission_form.cfm;status=Course Sharing Permission;image=;");
}
with(milonic=new menuname("Scantron")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Overview of Scantron;url=http://www2.oakland.edu/elis/scantron.cfm;status=Overview of Scantron;image=;");
aI("text=Quizzes and Exams;url=http://www2.oakland.edu/elis/scantron_tests.cfm;status=Quizzes and Exams;image=;");
aI("text=Course Evaluations;url=http://www2.oakland.edu/elis/scantron_evals.cfm;status=Course Evaluations;image=;");
aI("text=Assessments;url=http://www2.oakland.edu/elis/scantron_assess.cfm;status=Assessments;image=;");
}
with(milonic=new menuname("Web Development")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Web Application Development;url=http://www2.oakland.edu/elis/appdev.cfm;status=Web Application Development;");
aI("text=Web Site Prototyping and Support;url=http://www2.oakland.edu/elis/proto.cfm;status=Web Site Prototyping and Support;");
}
with(milonic=new menuname("Help Documents")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=WebCT Instructions;url=http://www2.oakland.edu/elis/docs_elearn.cfm;status=WebCT Instructions;");
aI("text=WebCT Help Videos;url=http://www2.oakland.edu/elis/upgrade.cfm;status=WebCT Help Videos;image=;");
aI("text=Moodle Instructions;url=http://www2.oakland.edu/elis/moodlehelp.cfm;status=Moodle Instructions;image=;");
aI("text=Web Development Help;url=http://www2.oakland.edu/elis/docs.cfm;status=Web Development Help;");
aI("text=Policies and Procedures;url=http://www2.oakland.edu/elis/policies.cfm;status=Policies and Procedures;image=;");
}
with(milonic=new menuname("Workshops")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Student Workshops;url=http://www2.oakland.edu/elis/WSO_enroll.cfm;status=Student Workshops;");
aI("text=Faculty Workshops;url=http://www2.oakland.edu/elis/training.cfm;status=Faculty Workshops;");
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
}
with(milonic=new menuname("WebCT SO")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Welcome;url=http://www2.oakland.edu/elis/WSO_index.cfm;status=Welcome;image=;");
aI("text=Online Courses;url=http://www2.oakland.edu/elis/WSO_courses.cfm;status=Online Courses;image=;");
aI("text=Technical Requirements;url=http://www2.oakland.edu/elis/WSO_techreq.cfm;status=Technical Requirements;image=;");
aI("text=FAQ;url=http://www2.oakland.edu/elis/WSO_faq.cfm;status=FAQ;image=;");
aI("text=Login Procedures;url=http://www2.oakland.edu/elis/WSO_login.cfm;status=Login Procedures;image=;");
aI("text=Demo Course;url=http://www2.oakland.edu/elis/WSO_demo.cfm;status=Demo Course;image=;");
aI("text=Online Self-Assessment Test;url=http://www2.oakland.edu/elis/WSO_assessment.cfm;status=Online Self-Assessment Test;image=;");
aI("text=Evaluation;url=http://www2.oakland.edu/elis/WSO_eval.cfm;status=Evaluation;image=;");
aI("text=Student Workshops;url=http://www2.oakland.edu/elis/WSO_enroll.cfm;status=Student Workshops;image=;");
}
with(milonic=new menuname("Moodle")){
style=submenuStyle;
top="offset=2"
margin=3
aI("text=Login to Moodle;url=https://moodle.oakland.edu;status=Login to Moodle;image=;");
aI("text=TECHNICAL SUPPORT;type=header;align=center;status=TECHNICAL SUPPORT;image=;");
aI("text=Request Help;url=http://www2.oakland.edu/elis/help.cfm;status=Request Help;image=;");
aI("text=Moodle Instructions;url=http://www2.oakland.edu/elis/moodlehelp.cfm;status=Moodle Instructions;image=;");
}
drawMenus();
