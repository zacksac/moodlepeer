<?php
/*ajax queries */
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->libdir.'/gradelib.php');
$dbconnect = mysql_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass) or die('unable to connect to sql server');
$dbselect  = mysql_select_db($CFG->dbname) or die('unable to select the moodle database');

 
if(isset($_GET['method'])){
    $postvars=$_GET; 
  
  
	$_GET['method']($postvars,$USER,$CFG);

}

function get_stat($postvars,$USER,$CFG){
  $cmid=$postvars['cm_id'];
  $userid=$postvars['user_id'];

  $qry_by="select * from peer_user_requests where userid=$userid and cmid=$cmid and state='reviewed' and peer_id!=0";
             $runby=mysql_query($qry_by);
             $numby=mysql_num_rows($runby);


             $qry_of="select * from peer_user_requests where posted_by=$userid and cmid=$cmid and state='reviewed' and peer_id!=0";
             $runof=mysql_query($qry_of);
             $numof=mysql_num_rows($runof);

             $qry_gdoc="select * from peer_user_requests where userid=$userid and cmid=$cmid and state='reviewed' and peer_id=0";
             $rungoc=mysql_query($qry_gdoc);
             $gdoc=mysql_num_rows($rungoc);
             $response['type']='success';
		$response['numby']= $numby;
		$response['numof']=$numof;
		$response['gdoc']=$gdoc;
		echo json_encode($response);

}



function save_peer($postvars,$USER,$CFG){
	
	$name=$postvars['peer_name'];
	$cmid=$postvars['cm_id'];
	$userid=$postvars['user_id'];
	$googleurl=$postvars['google_url'];
	   $check="select * from peer_reviews where user_id=$userid and cm_id=$cmid ";
	   $runcheck=mysql_query($check);
	   if(mysql_num_rows($runcheck)>0)
	   {
	   	$response="Sorry you have already submitted for this course";

	   }
	   else
	   {
			$qry="insert into peer_reviews (name,user_id,cm_id,google_url) values ('$name',$userid,$cmid,'$googleurl')";
		    if(mysql_query($qry)){
		    	$response="your review has been sumitted";
               /* enter the submitted peer to rf0 */
               $rfr0_peerid=mysql_insert_id();
             $q_pr0="insert into rfr0 (peer_id,userid,cmid,url) values ($rfr0_peerid,$userid,$cmid,'$googleurl') ";
             mysql_query($q_pr0);
               

		    }
	       }
    echo $response;
}

function request_peer($postvars,$USER,$CFG){
	global $DB;
    $name=$postvars['peer_name'];
	$cmid=$postvars['cm_id'];
	$userid=$postvars['user_id'];
	$response=array();


	/* update queue if time limit is more than 240 mins */
     $qry_time="SELECT id,peer_id,posted_by,cmid,userdoc,TIMESTAMPDIFF(MINUTE,timestart,now()) as diff FROM peer_user_requests where state!='reviewed' and peer_id!=0 ";
     $run_qry=mysql_query($qry_time);
     while($row=mysql_fetch_array($run_qry))
     {
     	if($row['diff']>=240)
     	{

     		$t_peerid=$row['peer_id'];
     		$t_postedby=$row['posted_by'];
     		$t_userdoc=$row['userdoc'];
     		$t_cmid=$row['cmid'];
     		$qry_update="insert into rfr4 (peer_id,userid,cmid,url,state) values($t_peerid,$t_userid,$t_cmid,$t_userdoc,0)";
     		mysql_query($qry_update);

     	}
     }

	/* end of queue update */
    
     /* check if the previous document is submitted */
	$chek_sub="select * from peer_reviews where user_id=$userid and cm_id=$cmid";
	if(mysql_num_rows(mysql_query($chek_sub))==0)
	{
		$response['type']='error';
		$response['message']= 'Please sumbit a doc for review';
		echo json_encode($response);
		die();
	}
	/*end of check of previous document */

	/*check if previous review is complete or not */
     $chek_req="select * from peer_user_requests where userid=$userid and cmid=$cmid";
	$qry_req=mysql_query($chek_req);
	while($row=mysql_fetch_array($qry_req))
	{
		 $previous_state=$row['state'];
		if($previous_state=='requested')
		{
			$response['type']='error';
		$response['message']= 'Please complete the previous review before requesting';
		echo json_encode($response);
		die();
		}

	}
	/*end of check */
	/*check if user has completed his 4 reviews */
      $check_num="select * from peer_user_requests where userid=$userid and cmid=$cmid and state='reviewed'";
     $run_num=mysql_query($check_num);
      $reviews_completed=mysql_num_rows($run_num);
     if(mysql_num_rows($run_num)>=5)
     {
     	$response['type']='error';
		$response['message']= 'You have completed your 5 reviews !';
		echo json_encode($response);
		die();

     }
	/*end of check */
     $user_req_peers=array();
	/* get all peer id reviewed by the user */
      $getreqpeers="select * from peer_user_requests where cmid=$cmid and userid=$userid";
     $rungetreqpeers=mysql_query($getreqpeers);
     while($row=mysql_fetch_array($rungetreqpeers))
     {
        $user_req_peers[]=$row['peer_id'];
     }
      if(mysql_num_rows($rungetreqpeers)==0)
      {
      	$user_req_peers[]=0;
      }
      
      $exclude_peers = implode(',',$user_req_peers);

	/* end of getting all the peer ids */
    

    /* check user can review real documents */
	 /* check if user has done more than 3 reviews */
     
    if ($reviews_completed >= 3) {
     $get_req = "select * from rfr1 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
     $run_get = mysql_query($get_req);
     if (mysql_num_rows($run_get) > 0) {
     	while($row=mysql_fetch_array($run_get))
     	{
     		$url=$row['url'];
     		$rfid=$row['id'];
     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
     	}
     	$response['type']="success";
        $response['rfrid']=$rfid;
		$response['rfrno']='rfr1';
	    $response['message']=$url;
	    $qry_updatestate="update rfr1 set state=1 where id=$rfid";
	    mysql_query($qry_updatestate);
	    /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr1',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
	    echo json_encode($response);

     } else {
         $get_req = "select * from rfr0 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
         $run_get = mysql_query($get_req);
         if (mysql_num_rows($run_get) > 0) {

         	while($row=mysql_fetch_array($run_get))
		     	{
		     		$url=$row['url'];
		     		$rfid=$row['id'];
		     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
		     	}
		     	$response['type']="success";
		        $response['rfrid']=$rfid;
				$response['rfrno']='rfr0';
			    $response['message']=$url;
			    $qry_updatestate="update rfr0 set state=1 where id=$rfid";
			    mysql_query($qry_updatestate);
			    /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr0',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
			    echo json_encode($response);

         } else {
             $get_req = "select * from rfr2 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
             $run_get = mysql_query($get_req);
             if (mysql_num_rows($run_get) > 0) {
             	while($row=mysql_fetch_array($run_get))
		     	{
		     		$url=$row['url'];
		     		$rfid=$row['id'];
		     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
		     	}
		     	$response['type']="success";
		        $response['rfrid']=$rfid;
				$response['rfrno']='rfr2';
			    $response['message']=$url;
			    $qry_updatestate="update rfr2 set state=1 where id=$rfid";
			    mysql_query($qry_updatestate);
			    /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr2',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
			    echo json_encode($response);

             } else {
                 $get_req = "select * from rfr3 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
                 $run_get = mysql_query($get_req);
                 if (mysql_num_rows($run_get) > 0) {
                 	while($row=mysql_fetch_array($run_get))
			     	{
			     		$url=$row['url'];
			     		$rfid=$row['id'];
			     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
			     	}
			     	$response['type']="success";
			        $response['rfrid']=$rfid;
					$response['rfrno']='rfr3';
				    $response['message']=$url;
				    $qry_updatestate="update rfr3 set state=1 where id=$rfid";
			        mysql_query($qry_updatestate);
			        /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr3',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
				    echo json_encode($response);

                 } else {
                     $get_req = "select * from rfr4 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
                     $run_get = mysql_query($get_req);
                     if (mysql_num_rows($run_get) > 0) {

                     	while($row=mysql_fetch_array($run_get))
				     	{
				     		$url=$row['url'];
				     		$rfid=$row['id'];
				     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
				     	}
				     	$response['type']="success";
				        $response['rfrid']=$rfid;
						$response['rfrno']='rfr4';
					    $response['message']=$url;
					    $qry_updatestate="update rfr4 set state=1 where id=$rfid";
			            mysql_query($qry_updatestate);
			            /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr4',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
					    echo json_encode($response);

                     } else {
                         /*get from sdfr */
                         /*get from sdfr */
                            /* check if one standard doc is already used */
                             $chk_doc="select * from peer_user_requests where peer_id=0 and userid=$userid and cmid=$cmid";
                            $runchkdoc=mysql_query($chk_doc);
                            if(mysql_num_rows($runchkdoc)>0)
                            {
                            	 $response['type']="error";
                            	 $response['message']="There is not work available now. Retry in a few hours please.";
                                 echo json_encode($response);
                                 break;
                            }
                            /* end of check */
                          $id=$cmid;
					      	$cm         = get_coursemodule_from_id('peer', $id, 0, false, MUST_EXIST);
							$peer  = $DB->get_record('peer', array('id' => $cm->instance), '*', MUST_EXIST);
							
					        $standard_docs=$peer->standarddocs;
					         if($standard_docs=='')
					        {
					          $response['type']="error";

					          $response['message']="no standard docs in the course";
					          echo json_encode($response);
					          break;
					        }
					          $get_docs="select * from peer_user_standarddocs where  cmid=$cmid";
					         $run_docs=mysql_query($get_docs);
					         if(mysql_num_rows($run_docs)>0)
					         {
					         	 $standard_docs_exist=1;
					         	 while($rows=mysql_fetch_array($run_docs)){
					         	 $standard_docs=$rows['standarddocs'];	
					         	}
					           
					         }
                               $standard_docs=explode(',',$standard_docs);
					          //print_r($standard_docs);
					          $first_ele = array_shift($standard_docs);
					          $response['type']="success";

					          $response['message']=$first_ele;
					          $response['rfrid']=0;
					          $response['rfrno']='';
					      
					          array_push($standard_docs,$first_ele);
					           $user_sdocs=implode(', ', $standard_docs);
					          $qry="insert into peer_user_requests (userid,cmid,state,userdoc) values ($userid,$cmid,'requested','$first_ele')";
                              mysql_query($qry);
                              if($standard_docs_exist==0)
					          {
					            $qry="insert into peer_user_standarddocs (cmid,standarddocs) values ($cmid,'$user_sdocs')";
					          mysql_query($qry);
					          }else
					          {
					           $qry="update peer_user_standarddocs set standarddocs='$user_sdocs' where  cmid=$cmid";
					          	mysql_query($qry);
					          }
					            echo json_encode($response);

                     }

                 }

             }
         }


     }

 } else
 /*user has not done more than 3 reviews */
 {  
 	$get_req = "select * from rfr4 where userid!=$userid and and peer_id not in ($exclude_peers) state=0 limit 1";
     $run_get = mysql_query($get_req);
     if (mysql_num_rows($run_get) > 0) {
     	while($row=mysql_fetch_array($run_get))
     	{
     		$url=$row['url'];
     		$rfid=$row['id'];
     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
     	}
     	$response['type']="success";
        $response['rfrid']=$rfid;
		$response['rfrno']='rfr4';
	    $response['message']=$url;
	    $qry_updatestate="update rfr4 set state=1 where id=$rfid";
	    mysql_query($qry_updatestate);
	    /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr4',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
	    echo json_encode($response);

     } else {
         $get_req = "select * from rfr3 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
         $run_get = mysql_query($get_req);
         if (mysql_num_rows($run_get) > 0) {
         	while($row=mysql_fetch_array($run_get))
		     	{
		     		$url=$row['url'];
		     		$rfid=$row['id'];
		     		$rfpeerid=$row['peer_id'];
				    $p_url=$row['url'];
				    $p_rqby=$row['userid'];
		     	}
		     	$response['type']="success";
		        $response['rfrid']=$rfid;
				$response['rfrno']='rfr3';
			    $response['message']=$url;
			    $qry_updatestate="update rfr3 set state=1 where id=$rfid";
			    mysql_query($qry_updatestate);
			    /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr3',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
			    echo json_encode($response);

         } else {
             $get_req = "select * from rfr2 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
             $run_get = mysql_query($get_req);
             if (mysql_num_rows($run_get) > 0) {

             		while($row=mysql_fetch_array($run_get))
			     	{
			     		$url=$row['url'];
			     		$rfid=$row['id'];
			     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
			     	}
			     	$response['type']="success";
			        $response['rfrid']=$rfid;
					$response['rfrno']='rfr2';
				    $response['message']=$url;
				    $qry_updatestate="update rfr2 set state=1 where id=$rfid";
			        mysql_query($qry_updatestate);
			        /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr2',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
				    echo json_encode($response);

             } else {
                 $get_req = "select * from rfr1 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
                 $run_get = mysql_query($get_req);
                 if (mysql_num_rows($run_get) > 0) {

                 	 while($row=mysql_fetch_array($run_get))
			     	{
			     		$url=$row['url'];
			     		$rfid=$row['id'];
			     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
			     	}
			     	$response['type']="success";
			        $response['rfrid']=$rfid;
					$response['rfrno']='rfr1';
				    $response['message']=$url;
				     $qry_updatestate="update rfr1 set state=1 where id=$rfid";
			        mysql_query($qry_updatestate);
			        /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr1',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
				    echo json_encode($response);

                 } else {
                     $get_req = "select * from rfr0 where userid!=$userid and state=0 and peer_id not in ($exclude_peers) limit 1";
                     $run_get = mysql_query($get_req);
                     if (mysql_num_rows($run_get) > 0) {
                     	while($row=mysql_fetch_array($run_get))
				     	{
				     		$url=$row['url'];
				     		$rfid=$row['id'];
				     		$rfpeerid=$row['peer_id'];
				     		$p_url=$row['url'];
				     		$p_rqby=$row['userid'];
				     	}
				     	$response['type']="success";
				        $response['rfrid']=$rfid;
						$response['rfrno']='rfr0';
					    $response['message']=$url;
					    $qry_updatestate="update rfr0 set state=1 where id=$rfid";
			            mysql_query($qry_updatestate);
			            /*update peer request */
			             $qry="insert into peer_user_requests (userid,peer_id,cmid,state,userdoc,rfr_id,rfr_no,posted_by) values ($userid,$rfpeerid,$cmid,'requested','$p_url',$rfid,'rfr0',$p_rqby)";
                         mysql_query($qry);
			            /* end of update of peer request */
					     echo json_encode($response);

                     } else {
                         /*get from sdfr */
                            /* check if one standard doc is already used */
                             $chk_doc="select * from peer_user_requests where peer_id=0 and userid=$userid and cmid=$cmid";
                            $runchkdoc=mysql_query($chk_doc);
                            if(mysql_num_rows($runchkdoc)>0)
                            {
                            	 $response['type']="error";
                            	 $response['message']="There is not work available now. Retry in a few hours please.";
                                 echo json_encode($response);
                                 break;
                            }
                            /* end of check */
                            $id=$cmid;
					      	$cm         = get_coursemodule_from_id('peer', $id, 0, false, MUST_EXIST);
							$peer  = $DB->get_record('peer', array('id' => $cm->instance), '*', MUST_EXIST);
							
					        $standard_docs=$peer->standarddocs;
					        if($standard_docs=='')
					        {
					          $response['type']="error";

					          $response['message']="no standard docs in the course";
					          echo json_encode($response);
					          break;
					        }


					         $get_docs="select * from peer_user_standarddocs where  cmid=$cmid";
					         $run_docs=mysql_query($get_docs);
					         if(mysql_num_rows($run_docs)>0)
					         {
					         	 $standard_docs_exist=1;
					         	 while($rows=mysql_fetch_array($run_docs)){
					         	 $standard_docs=$rows['standarddocs'];	
					         	}
					           
					         }
                               $standard_docs=explode(',',$standard_docs);
					          //print_r($standard_docs);
					          $first_ele = array_shift($standard_docs);
					          $response['type']="success";

					          $response['message']=$first_ele;
					          $response['rfrid']=0;
					          $response['rfrno']='';
					      
					          array_push($standard_docs,$first_ele);
					           $user_sdocs=implode(', ', $standard_docs);
					          $qry="insert into peer_user_requests (userid,cmid,state,userdoc) values ($userid,$cmid,'requested','$first_ele')";
                              mysql_query($qry);
                              if($standard_docs_exist==0)
					          {
					            $qry="insert into peer_user_standarddocs (cmid,standarddocs) values ($cmid,'$user_sdocs')";
					          mysql_query($qry);
					          }else
					          {
					           $qry="update peer_user_standarddocs set standarddocs='$user_sdocs' where  cmid=$cmid";
					          	mysql_query($qry);
					          }
					            echo json_encode($response);
                     }

                 }

             }
         }


     }

 }
	 /* end */			

   

}




function submit_peer($postvars,$USER,$CFG){
global $DB;
    $peer_url=$postvars['peer_url'];
	$cmid=$postvars['cm_id'];
	$userid=$postvars['user_id'];
	$rfrid=$postvars['rfrid'];
	$rfrno=$postvars['rfrno'];
	$peer_grade=$postvars['grade'];
	
	$response=array();

	 $qry_up="update peer_user_requests set state='reviewed' ,grade='$peer_grade' where userid=$userid and cmid=$cmid and rfr_id=$rfrid and rfr_no='$rfrno'";
	 mysql_query($qry_up);
     

     /*update completed reviews count */
       $qry_update_count="select * from peer_user_requests where userid=$userid and cmid=$cmid and rfr_id=$rfrid and rfr_no='$rfrno'";
       $run_qryupdate=mysql_query($qry_update_count);
       while($rowupdate=mysql_fetch_array($run_qryupdate))
       {
       	$peer_update_id=$rowupdate['peer_id'];
       }
       $update_c="update peer_reviews set completed_count=completed_count+1 where id=$peer_update_id";
       $run_updatec=mysql_query($update_c);
      
     /*end of updating peer reviews count */

	
     /*get user peer*/
      $qry_get="select * from peer_reviews where user_id=$userid and cm_id=$cmid";
      $run_get=mysql_query($qry_get);
      while($row=mysql_fetch_array($run_get))
      {
      	 $upid=$row['id'];
      	 $ugurl=$row['google_url'];
      }
     /*end*/
     /*get no of user reqest */
      $qry_up="select * from peer_user_requests where state='reviewed' and userid=$userid and $cmid=$cmid ";
	  $run_up=mysql_query($qry_up);
	  $no_of_reviews_done=mysql_num_rows($run_up);

	  if($no_of_reviews_done==1)
	  {
         $update_queue="insert into rfr1 (peer_id,userid,cmid,url,state) values ($upid,$userid,$cmid,'$ugurl',0)";
	    mysql_query($update_queue);
	    $response['type']='success';
		$response['message']='updated';
	
	  }
	  else if($no_of_reviews_done==2)
	  { $update_queue="insert into rfr2 (peer_id,userid,cmid,url,state) values ($upid,$userid,$cmid,'$ugurl',0)";
	    mysql_query($update_queue);
	    $response['type']='success';
		$response['message']='updated';

	  }
	  else if($no_of_reviews_done==3)
	  {
         $update_queue="insert into rfr3 (peer_id,userid,cmid,url,state) values ($upid,$userid,$cmid,'$ugurl',0)";
	    mysql_query($update_queue);
	    $response['type']='success';
		$response['message']='updated';
	  }
	  else if($no_of_reviews_done==4)
	  {
        $update_queue="insert into rfr4 (peer_id,userid,cmid,url,state) values ($upid,$userid,$cmid,'$ugurl',0)";
	    mysql_query($update_queue);
	    $response['type']='success';
		$response['message']='updated';
	  }
	  else
	  {
        $response['type']='success';
		$response['message']='updated';
	  }
	  echo json_encode($response);
     /* end of get request */
     

	 /* check peer completion for users */
      $check_r_done="select * from peer_user_requests where userid=$userid and peer_id!=0  and cmid=$cmid and state='reviewed'";
      $run_r=mysql_query($check_r_done);
      if(mysql_num_rows($run_r)>=4)
      {
      	

       	$qry_checkif="select * from peer_reviews where cm_id=$cmid and user_id=$userid and completed_count >=3";
      	$run_checkif=mysql_query($qry_checkif);
      	while($row_checkif=mysql_fetch_array($run_checkif))
      	{
      		 $completeduserreviews=$row_checkif['completed_count'];
      		 if($completeduserreviews>=3)
      		 {
      		 	           /* update peer for user */
      		 	            $id=$cmid;
					      	$cm         = get_coursemodule_from_id('peer', $id, 0, false, MUST_EXIST);
							$peer  = $DB->get_record('peer', array('id' => $cm->instance), '*', MUST_EXIST);
                            peer_update_grades($peer,$userid);
      		 	
      		 	            /* end of peer update */

      		 	            /* moove user peers to rfr0 */
      		 	           
      		 	            $qry ="select * from rfr1 where userid=$userid and cmid=$cmid and state=0";
                            $runqry=mysql_query($qry);
                            if(mysql_num_rows($runqry)>0)
                            {
	                            while($row=mysql_fetch_array($runqry)){
                                 $r_pid=$row['peer_id'];
                                 $r_url=$row['url'];
                                 $r_id=$row['id'];

	                        }
	                            $insert_rfr0="insert into rfr0 (peer_id,userid,cmid,url,state) values ($r_pid,$userid,$cmid,$r_url,0)";
	                            mysql_query($insert_rfr0);
                                $qry_d="delete from rfr1 where id=$r_id";
                                mysql_query($qry_d);

                            }

                            $qry ="select * from rfr2 where userid=$userid and cmid=$cmid and state=0";
                            $runqry=mysql_query($qry);
                            if(mysql_num_rows($runqry)>0)
                            {
	                            while($row=mysql_fetch_array($runqry)){
                                 $r_pid=$row['peer_id'];
                                 $r_url=$row['url'];
                                 $r_id=$row['id'];

	                            }
	                            $insert_rfr0="insert into rfr0 (peer_id,userid,cmid,url,state) values ($r_pid,$userid,$cmid,$r_url,0)";
	                            mysql_query($insert_rfr0);
                                $qry_d="delete from rfr2 where id=$r_id";
                                mysql_query($qry_d);

                            }

                            $qry ="select * from rfr3 where userid=$userid and cmid=$cmid and state=0";
                            $runqry=mysql_query($qry);
                            if(mysql_num_rows($runqry)>0)
                            {
	                            while($row=mysql_fetch_array($runqry)){
                                 $r_pid=$row['peer_id'];
                                 $r_url=$row['url'];
                                 $r_id=$row['id'];

	                            }
	                            $insert_rfr0="insert into rfr0 (peer_id,userid,cmid,url,state) values ($r_pid,$userid,$cmid,$r_url,0)";
	                            mysql_query($insert_rfr0);
                                $qry_d="delete from rfr3 where id=$r_id";
                                mysql_query($qry_d);

                            }

                            $qry ="select * from rfr4 where userid=$userid and cmid=$cmid and state=0";
                            $runqry=mysql_query($qry);
                            if(mysql_num_rows($runqry)>0)
                            {
	                            while($row=mysql_fetch_array($runqry)){
                                 $r_pid=$row['peer_id'];
                                 $r_url=$row['url'];
                                 $r_id=$row['id'];

	                            }
	                            $insert_rfr0="insert into rfr0 (peer_id,userid,cmid,url,state) values ($r_pid,$userid,$cmid,$r_url,0)";
	                            mysql_query($insert_rfr0);
                                $qry_d="delete from rfr4 where id=$r_id";
                                mysql_query($qry_d);

                            }




                            

      		 	            /* end of move */
      		 }
      	} 


      }


	 /* end of check */
}
?>