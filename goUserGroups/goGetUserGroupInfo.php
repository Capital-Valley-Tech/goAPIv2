<?php
/**
 * @file 		goGetUserGroupInfo.php
 * @brief 		API to get specific User Group Details
 * @copyright 	Copyright (c) 2018 GOautodial Inc.
 * @author		Demian Lizandro A. Biscocho <demian@goautodial.com> 
 * @author     	Alexander Jim H. Abenoja <alex@goautodial.com>
 *
 * @par <b>License</b>:
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

	include_once ("goAPI.php");
	
    // POST or GET Variables
    $user_group = $astDB->escape($_REQUEST['user_group']);
	
	$log_user = $session_user;
	$log_group = go_get_groupid($session_user, $astDB);
	$ip_address = $_REQUEST['log_ip'];
    
    if(!isset($session_user) || is_null($session_user)){
    	$apiresults = array("result" => "Error: Missing Required Parameters.");
    }elseif(is_null($user_group)) { 
		$apiresults = array("result" => "Error: Set a value for User Group."); 
	} else {

		if (!checkIfTenant($log_group, $goDB)) {
			$astDB->where("user_group", $user_group);		
            $group_type = "Multi-tenant";
		} else {
			$astDB->where("user_group", $user_group);
			$astDB->where("user_group", $log_group);			
        	$group_type = "Default";
		}
		
		$cols = array("user_group", "group_name", "allowed_campaigns", "forced_timeclock_login", "shift_enforcement", "admin_viewable_groups");
		$astDB->orderBy("user_group","asc");
		$astQuery = $astDB->getOne("vicidial_user_groups", NULL, $cols);
   		//$query = "SELECT user_group,group_name,forced_timeclock_login,shift_enforcement,allowed_campaigns,admin_viewable_groups FROM vicidial_user_groups $ul ORDER BY user_group LIMIT 1;";\
		
		$cols2 = array("group_level", "permissions");
		$goDB->where("user_group", $user_group);
		$goQuery = $goDB->getOne("user_access_group", NULL, $cols2);
		//$queryGL = "SELECT group_level,permissions FROM user_access_group WHERE user_group='$user_group';";
		
		$data = array($astQuery, $goQuery);
		
		$log_id = log_action($goDB, 'VIEW', $log_user, $ip_address, "Viewed the info of User Group: $user_group", $log_group);
		
		if(!empty($data)) {
            $apiresults = array("result" => "success", "data" => $data);
		} else {
			$apiresults = array("result" => "Error: User Group doesn't exist.");
		}
	}
?>
