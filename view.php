<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of peer
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_peer
 * @copyright  2015 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace peer with the name of your module and remove this line.


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/gradelib.php');
$dbconnect = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die('unable to connect to sql server');
$dbselect  = mysql_select_db($CFG->dbname) or die('unable to select the moodle database');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... peer instance ID - it should be named as the first character of the module.
$PAGE->requires->jquery();
if ($id) {
    $cm         = get_coursemodule_from_id('peer', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $peer  = $DB->get_record('peer', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $peer  = $DB->get_record('peer', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $peer->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('peer', $peer->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$event = \mod_peer\event\course_module_viewed::create(array(
    'objectid' => $PAGE->cm->instance,
    'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $peer);
$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/peer/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($peer->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('peer-'.$somevar);
 */

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
if ($peer->intro) {
    echo $OUTPUT->box(format_module_intro('peer', $peer, $cm->id), 'generalbox mod_introbox', 'peerintro');
}

// Replace the following lines with you own code.
$modtype='peer';
echo $OUTPUT->heading('Peer Review');
echo '<h3>Logged in as '.$USER->firstname .'</h3>';
            
      
            /* updating module completion using grades */

            $sql="update mdl_course_modules_completion set completionstate=1 where userid=$USER->id ";
            //$run=mysql_query($sql);
            $completion = new completion_info($course);
            //$x=peer_update_grades($peer,$USER->id);

            /*end of updating module completion */

            /*table creation check */
            $table_demo="CREATE TABLE IF NOT EXISTS peer_user_standarddocs (
              id int(11) NOT NULL AUTO_INCREMENT,
              cmid int(11) NOT NULL,
              userid int(11) NOT NULL,
              standarddocs text,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
            $run_demo=mysql_query($table_demo);

            $table_x="CREATE TABLE IF NOT EXISTS peer_reviews (
              id int(11) NOT NULL AUTO_INCREMENT,
              name varchar(11) NOT NULL,
             google_url varchar(22) NOT NULL,
              cm_id int(11) NOT NULL,
              user_id int(11) NOT NULL,
              completed_count int(11) NOT NULL DEFAULT 0,
              created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
              PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
            $run_x=mysql_query($table_x);


            $table_y="CREATE TABLE IF NOT EXISTS rfr0 (
          id int(11) NOT NULL AUTO_INCREMENT,
          peer_id int(11) NOT NULL,
          userid int(11) NOT NULL,
          cmid int(11) NOT NULL,
          url text NOT NULL,
          state int(11) NOT NULL DEFAULT 0,
          timestart timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
           $run_y=mysql_query($table_y);

            $table_y="CREATE TABLE IF NOT EXISTS rfr1 (
          id int(11) NOT NULL AUTO_INCREMENT,
          peer_id int(11) NOT NULL,
          userid int(11) NOT NULL,
          cmid int(11) NOT NULL,
          url text NOT NULL,
          state int(11) NOT NULL DEFAULT 0,
          timestart timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

           $run_y=mysql_query($table_y);
            $table_y="CREATE TABLE IF NOT EXISTS rfr2 (
          id int(11) NOT NULL AUTO_INCREMENT,
          peer_id int(11) NOT NULL,
          userid int(11) NOT NULL,
          cmid int(11) NOT NULL,
          url text NOT NULL,
          state int(11) NOT NULL DEFAULT 0,
          timestart timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

           $run_y=mysql_query($table_y);
           $table_y="CREATE TABLE IF NOT EXISTS rfr3 (
          id int(11) NOT NULL AUTO_INCREMENT,
          peer_id int(11) NOT NULL,
          userid int(11) NOT NULL,
          cmid int(11) NOT NULL,
          url text NOT NULL,
          state int(11) NOT NULL DEFAULT 0,
          timestart timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

           $run_y=mysql_query($table_y);
            $table_y="CREATE TABLE IF NOT EXISTS rfr4 (
          id int(11) NOT NULL AUTO_INCREMENT,
          peer_id int(11) NOT NULL,
          userid int(11) NOT NULL,
          cmid int(11) NOT NULL,
          url text NOT NULL,
          state int(11) NOT NULL DEFAULT 0,
          timestart timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
           $run_y=mysql_query($table_y);





          $table_y="CREATE TABLE IF NOT EXISTS peer_user_requests(
          id int(11) NOT NULL AUTO_INCREMENT,
          peer_id int(11) NOT NULL,
          userid int(11) NOT NULL,
          posted_by int(11) NOT NULL,
          cmid int(11) NOT NULL,
          state varchar(11) NOT NULL,
          userdoc text,
          rfr_id int(11) NOT NULL,
          rfr_no varchar(11) NOT NULL,
          grade int(11)  NULL DEFAULT NULL,
          timestart timestamp DEFAULT 0 NOT NULL,
          timeend timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
          $run_y=mysql_query($table_y);
            /*end of table creation check */

            /*custom links test*/
             
            ?>
           
             <div class="submit review generalbox  box">

             <div id="status">
             <h5><label> User statistics </label></h5>
             <?php
             $qry_by="select * from peer_user_requests where userid=$USER->id and cmid=$id and state='reviewed' and peer_id!=0";
             $runby=mysql_query($qry_by);
             $numby=mysql_num_rows($runby);


             $qry_of="select * from peer_user_requests where posted_by=$USER->id and cmid=$id and state='reviewed' and peer_id!=0";
             $runof=mysql_query($qry_of);
             $numof=mysql_num_rows($runof);

             $qry_gdoc="select * from peer_user_requests where userid=$USER->id and cmid=$id and state='reviewed' and peer_id=0";
             $rungoc=mysql_query($qry_gdoc);
             $gdoc=mysql_num_rows($rungoc);
             ?>

             No. of peers reviewed By me  : <span class="byme"><?php echo $numby;?></span> 
             <br>
             No. of peers reviewed Of me  :  <span class="ofme"><?php echo $numof;?></span>
             <br>
             No. of google docs used  :  <span class="gdoc"><?php echo $gdoc;?></span>
           
             </div>
             <hr>
             
             Submit google url for review <input type="text" name="google_url">
             <input type="hidden" name="cm_id" value="<?php echo $id;?>">
             <input type="hidden" name="peer_name" value="<?php echo $peer->name;?>">
             <input type="hidden" name="user_id" value="<?php echo $USER->id;?>">
             <button onclick="checkurl();">check url</button>
             <input type="button" onclick="submitreview();" value="Submit Review">
             
             <label id="alert_msg"></label>
             </div>
             
             <div class="request review generalbox box">
            
             <input type="hidden" name="cm_id1" value="<?php echo $id;?>">
             <input type="hidden" name="peer_name1" value="<?php echo $peer->name;?>">
             <input type="hidden" name="user_id1" value="<?php echo $USER->id;?>">
             <hr>
              Ready to take a review <button  onclick="requestreview();">Request review </button>
             <div class="review_url">

             <?php
              $qry_req="select * from peer_user_requests where cmid=$id and userid=$USER->id and state='requested' limit 1";
              $qry=mysql_query($qry_req);
              while($row=mysql_fetch_array($qry))
               {
                 echo "<a class='requested_info' data-no='".$row['rfr_no']."' data-id='".$row['rfr_id']."' href='".$row['userdoc']."'>".$row['userdoc']."</a><input type='text' name='submittedpeer'></input><button onclick='checkurl1();'' id='checkreview'>check url</button> Grade <select id='grade_peer'><option value='def' selected='selected'> Grade Peer</option><option value='0'>0</option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option></select><button onclick='submitpeerreview();' class='submit_review'>Submit My Review</button>";
               }
             ?>
               
             </div>
             <div id="requestedpeer">
             </div>
             <div class="request_status"></div>
             </div>
            <?php
            /*end of custom links test*/
// Finish the page.
echo $OUTPUT->footer();

// <script type = "text/javascript" 
//          src = "http://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>


?>

 <script type = "text/javascript" language = "javascript">
 var forcecheck=1;
 var submittedpeer=1;
        function checkurl(){
          var google_url=$('input[name="google_url"]').val();
           if (google_url=='')
            {
               $("#alert_msg").html('');
               $("#alert_msg").append("<div class='error'>Please enter your google doc url !!</div>");
               return 0;
            }

            window.open(google_url);
           forcecheck=0; 
        }
         function checkurl1(){
          var google_url=$('input[name="submittedpeer"]').val();
           if (google_url=='')
            {
               $(".request_status").html('');
               $(".request_status").append("<div class='error'>Please enter your google doc url !!</div>");
               return 0;
            }

            window.open(google_url);
           submittedpeer=0; 
        }
        function submitreview(){
            var google_url=$('input[name="google_url"]').val();
            var cm_id=$('input[name="cm_id"]').val();
            var user_id=$('input[name="user_id"]').val();
            var peer_name=$('input[name="peer_name"]').val();
            
            if (google_url=='')
            {
               $("#alert_msg").html('');
               $("#alert_msg").append("<div class='error'>Please enter your google doc url !!</div>");
               return 0;
            }
            if (forcecheck==1)
            {
              $("#alert_msg").html('');
               $("#alert_msg").append("<div class='error'>Please check the url before submitting</div>");
               return 0;

            }
            $.ajax({
               url: 'peer_api.php',
               data: {
                  method: 'save_peer',
                  google_url:google_url,
                  cm_id:cm_id,
                  user_id:user_id,
                  peer_name:peer_name
               },
               error: function() {
                  $('#info').html('<p>An error has occurred</p>');
               },
             
               success: function(data) {
                 $("#alert_msg").html('');
                 $("#alert_msg").append("<div class='success'>"+data+"</div>");
                 getstats();
                },
               type: 'GET'
            });
            $('#peer_submit input[name="google_url"]').val('');
         };

         function requestreview(){
            
            var cm_id=$('input[name="cm_id1"]').val();
            var user_id=$('input[name="user_id1"]').val();
            var peer_name=$('input[name="peer_name1"]').val();
            $.ajax({
               url: 'peer_api.php',
               data: {
                  method: 'request_peer',
                  cm_id:cm_id,
                  user_id:user_id,
                  peer_name:peer_name
               },
               error: function() {
                  $('#info1').html('<p>An error has occurred</p>');
               },
             
               success: function(data) {
                
                var obj = jQuery.parseJSON(data);
                
                    if(obj.type=='error')
                      {
                        $('.request_status').html("<label>"+obj.message+"</label>");
                      }
                      else 
                       {
                        $(".review_url").append("<a class='requested_info' data-no='"+obj.rfrno+"'  data-id='"+obj.rfrid+"' href='"+obj.message+"'>"+obj.message+"</a><input type='text' name='submittedpeer' ></input><button onclick='checkurl1();'' id='checkreview'>check url</button> Grade <select id='grade_peer'><option value='def' selected='selected'>  Grade Peer</option><option value='0'>0</option><option value='1'>1</option><option value='2'>2</option><option value='3'>3</option><option value='4'>4</option><option value='5'>5</option><option value='6'>6</option><option value='7'>7</option><option value='8'>8</option><option value='9'>9</option><option value='10'>10</option></select><button onclick='submitpeerreview();' class='submit_review'>Submit My Review</button>");
                        getstats();
                       }
                 
                
               },
               type: 'GET'
            });

         }


         function submitpeerreview(){
           
           var rfrid=$('.requested_info').attr('data-id');
           var rfrno=$('.requested_info').attr('data-no');
           var peer_url=$('input[name="submittedpeer"]').val();
           var cm_id=$('input[name="cm_id"]').val();
            var user_id=$('input[name="user_id"]').val();
            var gradepeer=$("#grade_peer").val();
            if (peer_url=='')
            {
               $(".request_status").html('');
               $(".request_status").append("<div class='error'>Please enter reviewed google doc url !!</div>");
               return 0;
            }
            if (submittedpeer==1)
            {
              $(".request_status").html('');
               $(".request_status").append("<div class='error'>Please check the url before submitting</div>");
               return 0;

            }
            if (gradepeer=='def')
            {
              $(".request_status").html('');
               $(".request_status").append("<div class='error'>Please grade the peer!</div>");
               return 0;

            }

             $.ajax({
               url: 'peer_api.php',
               data: {
                  method: 'submit_peer',
                  cm_id:cm_id,
                  user_id:user_id,
                  peer_url:peer_url,
                  rfrid:rfrid,
                  rfrno:rfrno,
                  grade:gradepeer
               },
               error: function() {
                  $('.request_status').html('<p>An error has occurred</p>');
               },
             
               success: function(data) {
                
                var obj = jQuery.parseJSON(data);
                
                    if(obj.type=='error')
                      {
                        $('.request_status').html("<label>"+obj.message+"</label>");
                      }
                      else 
                       {
                        $('.review_url').html('');
                        $('.request_status').html('peer sucssfully completed');
                        getstats();
                       }
                 
                
               },
               type: 'GET'
            });
         }


         function getstats()
         {
          var cm_id=$('input[name="cm_id"]').val();
          var user_id=$('input[name="user_id"]').val();
             $.ajax({
               url: 'peer_api.php',
               data: {
                  method: 'get_stat',
                  cm_id:cm_id,
                  user_id:user_id
                  
               },
               error: function() {
                  //$('.request_status').html('<p>An error has occurred</p>');
               },
             
               success: function(data) {
                
                var obj = jQuery.parseJSON(data);
                
                    if(obj.type=='error')
                      {
                        //$('.request_status').html("<label>"+obj.message+"</label>");
                      }
                      else 
                       {
                        $('.byme').html(obj.numby);
                        $('.ofme').html(obj.numof);
                        $('.gdoc').html(obj.gdoc);
                        
                        
                       }
                 
                
               },
               type: 'GET'
            });
         }


$(document).ready(function(){
  
 $('input[name="google_url"]').on('input',function(e){
    var google_url=$('input[name="google_url"]').val();
    if (google_url!='')
    {
      forcecheck=1;
    }
    });

  $('input[name="submittedpeer"]').on('input',function(e){
    var google_url=$('input[name="submittedpeer"]').val();
    if (google_url!='')
    {
      submittedpeer=1;
    }
    });
})

</script>
 

 <style>
 .success
 {
  color: green;
 }
 .error
 {
  color: red;
 }
 </style>