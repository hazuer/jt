<?php
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );
require_once( $GLOBALS['mosConfig_absolute_path'] . '/includes/memcache/registration.php' );
require_once( $mainframe->getPath( 'front_html' ) );
require_once( $GLOBALS['mosConfig_absolute_path'] . '/components/com_iboregistration/ibo_Registration.html.php' );
require_once( $GLOBALS['mosConfig_absolute_path'] . '/enzapi/enzapi.php' );
require_once( $GLOBALS['mosConfig_absolute_path'] . '/kakao/kakao.php' );
/**************************************************************************************************************
* Name   : IBO Registration
* Author :
* Date   :
* Desc   : For Registering New IBO's
*
*
****************************************************************************************************************/

   define('_STEP_TEMPPOST_KOREAN_COUNTRY_DATA', 35);
   define('_STEP_VALIDATE_KOREAN_COUNTRY_DATA', 4);

  global $mainframe;
  $step_number = mosgetparam( $_REQUEST, 'step_number', 0);
  $action_number = mosgetparam( $_REQUEST, 'action_number', 0);
  $regedit = mosgetparam( $_REQUEST, 'regedit', 'off');
  switch($action_number) {
      case "0":
      	  showMainModule();
		  unsetPromo($regedit);
      		break;
      case "1":

      	  showConfirmData();
      		break;
      case "2":
          SaveRegistration();
          break;
      case "3":
          showKoreanCountrySpecificData($_REQUEST);
          break;
      case _STEP_TEMPPOST_KOREAN_COUNTRY_DATA:
          tempPostKoreanCountrySpecificData();
          break;

      case _STEP_VALIDATE_KOREAN_COUNTRY_DATA:
          validateKoreanCountrySpecificData();
          break;
      case "50":
          showAgeVerification();
          break;
      case "51":
          showAgeVerificationError();
          break;
      case "52":
          showKoreaLegalNotice();
          break;

      case "53":
          PrintIndependentBusinessOwnerApplication();
          break;

  }

function showAgeVerification(){
  HTML_iboRegistration::showAgeVerification();
}

function showAgeVerificationError(){
  HTML_iboRegistration::showAgeVerificationError();
}

function showKoreaLegalNotice(){
  HTML_iboRegistration::showKoreaLegalNoticeKOR();
}

/**************************************************************************************************************
* Name   : showMainModule
* Author :
* Date   :
* Desc   : Shows The Header and Informations
****************************************************************************************************************/

function showMainModule() {
   global $database,$PHPSHOP_LANG,$mosConfig_lang,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe;

   $usertype="select usertype from mambophil_users where id=".$_SESSION["wwwuser"]->id;

   $memregistration = registration::newInstance($database);
   $tocken  = "ibo_Registration_63_".$_SESSION["wwwuser"]->id ;
   $user = $memregistration->ibo_Registration_63($usertype,$tocken,'object');

  // $database->setQuery($usertype);
  // $database ->loadObject($user);
   if($user->usertype=="Enzacta ABC Member")
   {
   echo "<font color='red'>"._NOT_AUTH."</font>";
   return;
   }

   $vendor_id = mosGetParam( $_REQUEST, 'country', '');
   $pibo = mosGetParam( $_REQUEST, 'pibo', '');
   $age_verified = mosGetParam( $_REQUEST, 'age_verified', null);
   $terms_agreed = mosGetParam( $_REQUEST, 'terms_agreed', null);

   if(!$vendor_id){
      $vendor_id =  $_SESSION["ps_vendor_id"];  // for public registration
   }
    // if($vendor_id == 10 && $korean_ssn != 1 && !$_REQUEST['editmode']){
    //   header('Location: '.$mosConfig_live_site.'?option=com_ibo&Itemid='.$_REQUEST["Itemid"].'&step_number=6001&action_number=3&country='.$vendor_id);
    //  }
   $currentUser = mosgetparam( $_SESSION, 'wwwuser', null);
   $currentUsers = $mainframe->getUser();

   //echo "Vendor: $vendor_id", "EDIT: ", $_REQUEST['editmode'], "TERSM:",$terms_agreed;

   if($vendor_id == 10 && !$_REQUEST['editmode'] && $terms_agreed != 1 && $currentUsers->gid != 3){
     showKoreaLegalNotice();
   }else{
     if(isset($_SESSION["wwwuser"]->username)) {
        HTML_iboRegistration::showMainModule();
     }elseif($currentUsers->gid == "3"){
        HTML_iboRegistration::showMainModule();
     }else{
        HTML_iboRegistration::enterIBONumber();
     }
   }

   unset($_SESSION['auto_credit_card']);
}
function unsetPromo($regedit){
	if($regedit=='off'){
	if(isset($_SESSION['jpitem'])){
		unset($_SESSION['jpitem']);
	}
	if (isset($_SESSION['jpsignupitem'])) {
		unset($_SESSION['jpsignupitem']);
	}
	}
}
function showConfirmData($korean_ssn = null, $korean_ssn_error = null){
if(isset($_REQUEST['promoitem_list'])){
	unset($_SESSION['jpitem']);
	$_SESSION['jpitem'] = array();
	foreach($_REQUEST['promoitem_list'] as $promo){
		/*if (!isset($_SESSION['jpitem'])) {
			$_SESSION['jpitem'] = array();
		}*/
		array_push($_SESSION['jpitem'], $promo);
	}
}
if(isset($_REQUEST['joinitem_promo'])){
	$_SESSION['jpsignupitem'] = $_REQUEST['joinitem_promo'];
}
 if($korean_ssn) {
  $KOREAN_REG_VALUES = unserialize($_SESSION['KOREAN_REG_VALUES']);
   if($KOREAN_REG_VALUES['country'] == 10) {
      $_TEMP_REQUEST = $_REQUEST;
     $_REQUEST = $KOREAN_REG_VALUES ;
     $_REQUEST['korean_ssn'] =  $korean_ssn;
     $_REQUEST['korean_ssn_error'] =  $korean_ssn_error;
     $_REQUEST['jumin1'] = $_TEMP_REQUEST['jumin1'];
     $_REQUEST['jumin2'] = $_TEMP_REQUEST['jumin2'];
  }
 }
$rtn = validateregistration($message);



$Originaluser_id = mosgetparam( $_REQUEST, 'Originaluser_id', 0);
if($_SESSION['auth']['user_id'] != $Originaluser_id)
{
	if (isset($_SESSION['jpitem'])) {
		unset($_SESSION['jpitem']);
	}
	if (isset($_SESSION['jpsignupitem'])) {
		unset($_SESSION['jpsignupitem']);
	}
	die('<script>alert("'._SESSION_EXPIRED_REGISTER.'");location.href="'.$mosConfig_live_site.'"</script>');
}
    if(!$rtn["foundError"]){
 HTML_iboRegistration::showConfirmationScreen();
 }
 else{
        HTML_iboRegistration::serializeallvalues($_REQUEST);
         $_REQUEST["editmode"]="1";
         //HTML_iboRegistration::showTitle($rtn["errorMessage"],"error",$rtn["errorSteps"]);
         HTML_iboRegistration::showMainModule($rtn["errorMessage"],"error",$rtn["errorSteps"]);

 }

}



function validateregistration(&$message)
 {

    global $PHPSHOP_LANG,$mosConfig_absolute_path,$database,$mainframe;
    require_once( $mosConfig_absolute_path.'/components/com_advanced_registration/advanced_registration_functions.php' );

    require_once(CLASSPATH . 'ps_checkout.php');
    $ps_checkout = new ps_checkout;
    $currentUser = $mainframe->getUser();
    $foundError = false;
    $errorSteps = Array();
    $action = mosgetparam( $_REQUEST, 'action', '');
    $_REQUEST["editmode"]= 1;
    $_REQUEST["editcountry"]= $_REQUEST["country"];
    $vendor_id = mosGetParam( $_REQUEST, 'country', '');
//   print_r($_REQUEST);
   //this funtion is used to get the Order amount and this is needed to check for multiple payment
    $order_amount=HTML_iboRegistration::taxandtotalofallproducts($_REQUEST);
      //echo "$order_amount";
      //if($vendor_id==1){
      //$order_amount=floor($order_amount);
      //}
    $country = "SELECT v.vendor_id,c.country_name as country,c.country_3_code as codes,c.country_id as contryid
                  from  mambophil_pshop_country c
                  INNER JOIN mambophil_pshop_vendor  v on c.country_3_code=v.vendor_country
                  WHERE v.vendor_id =$vendor_id";
                  //echo $country;
              $countryID= $_REQUEST["country"];
              //echo $countryID;
        $database->setQuery($country );
        $database ->loadObject($dbcountry);
          $country_id=$dbcountry->contryid;
        $country_flag=$dbcountry->codes;
         $enrolType = mosgetparam( $_REQUEST, 'enrolType', '');
       if($vendor_id == "10") {
            // Korean Age validation is required to do
            $type = ($enrolType=="Individual") ? "ind" : "bus";

            // Validate Age
          /*  $byear = $_REQUEST[$type.'byear'];
            $today = date("Y");
            $age = $today-$byear;
            //echo "$jumin1 | $seventh_digit | $today | $byear | AGE: $age";
            if($age < 20 && $currentUser->gid != "3"){
              $foundError = true;
              $tempErrStep = true;
              $message .= "<br>&nbsp;&nbsp;&nbsp;- "._REGISTRATION_AGE_ERROR;
              $message .= "<br>";
              $errorSteps[] = 0;
            }*/
            // IBO age limit in Korean IBO site  JIRA ENZ-3257
            $byear = $_REQUEST[$type.'byear'];
            $bmonth = $_REQUEST[$type.'bmonth'];
            $bdate = $_REQUEST[$type.'bdate'];
            if(!empty($byear) && !empty($bmonth) && !empty($bdate) ){ // korean IBO site with valid birth date
              $dateOfBirth = $byear.'-'.$bmonth.'-'.$bdate;
              $datetime = new DateTime("now", new DateTimeZone('Asia/Seoul'));
              $currdate = $datetime->format('Y-m-d');
              $diff = date_diff(date_create($dateOfBirth), date_create($currdate));
              $age_y = $diff->y;  $age_m = $diff->m;  $age_d = $diff->d;              
              //echo "Your current age is ".$diff->y." years ".$diff->m." months ".$diff->d." days.";
              if($age_y >= 19 ){
               // age eligible for join
              } else {
                 $foundError = true;
                 $tempErrStep = true;
                 $message .= "<br>&nbsp;&nbsp;&nbsp;- "._REGISTRATION_AGE_ERROR;//_REGISTRATION_AGE_LIMIT_ERROR;
                 $message .= "<br>";
                 $errorSteps[] = 0;
              }
              
            }

            // Korean Bank Validation JIRA ENZ-501
            $byear = $_REQUEST[$type.'byear'];
            $bmonth = $_REQUEST[$type.'bmonth'];
            $bdate = $_REQUEST[$type.'bdate'];
            // For Korea make first part(6 digit) of SSN from date of birth
            $by = substr($byear, -2);
            $bm = sprintf("%02d", $bmonth);
            $bd = sprintf("%02d", $bdate);
            $strResId = $by.$bm.$bd; // 6 digit resident ID

            $strBankCode = $_REQUEST[$type.'bank_name'];
            //$_REQUEST[$type.'bank_account_nr']=str_replace('-','',$_REQUEST[$type.'bank_account_nr']);
            $strAccountNo = str_replace('-','',$_REQUEST[$type.'bank_account_nr']);
            $koreanName = trim($_REQUEST[$type.'bank_account_holder']);				
            $strGbn = '1';                 
          
            if(!empty($strAccountNo) && !empty($koreanName) && !empty($strResId) && $_REQUEST["skipssn"] != 1){ 
                $bank_details = ValidateBankAccount($strResId, $strBankCode, $strAccountNo, $koreanName, $strGbn);

                if(!$bank_details){
                  $foundError = true;
                  $tempErrStep = true;
                  $message .= "<br>&nbsp;&nbsp;&nbsp;- "._REG_ERR_VALID_BANKDETAILS." - ";
                  $message .= isset($_REQUEST['bank_error_msg']) ? $_REQUEST['bank_error_msg'] : '';
                  $message .= "<br>";
                }
            }

            $szBirth = $byear.$bm.$bd;
            $szSex = $_REQUEST[$type.'gender'];

            //echo "$koreanName|$szBirth|$szSex";
            if(!empty($koreanName) && !empty($szBirth) && !empty($szSex) && $_REQUEST["skipssn"] != 1){
              $name_result = ValidateKoreaNameBirth($koreanName, $szBirth, $szSex);

              //var_dump($name_result);
              if($name_result['is_valid'] != true){
                  $foundError = true;
                  $tempErrStep = true;
                  $message .= "<br>&nbsp;&nbsp;&nbsp;- ".$PHPSHOP_LANG->_PHPSHOP_USER_LIST_FULL_NAME." - ";
                  $message .= isset($name_result['message']) ? $name_result['message'] : '';
                  $message .= "<br>";
              }
            }

            // Validate if the Birthday and Name already exist in the system  JIRA ENZ-501
            $fullBirth = $byear.'-'.$bm.'-'.$bd;
            if(!empty($koreanName) && !empty($fullBirth)) {
              $sql = "SELECT COUNT(u.username) AS UserCount FROM mambophil_users u
                      INNER JOIN mambophil_adv_users au ON au.id = u.id
                      WHERE u.`kssn_name` = '$koreanName' AND u.usertype = 'Enzacta IBO' AND u.bday = '$fullBirth' AND au.status_cd != 'C'";
              $database->setQuery($sql);
              $rtn_user = null;
              $database ->loadObject($rtn_user);
              if($rtn_user->UserCount > 0){
                  $foundError = true;
                  $tempErrStep = true;
                  $message .= "<br>&nbsp;&nbsp;&nbsp;- "._KOREAN_SSN_EXISTS;
                  $message .= "<br>";
                  $errorSteps[] = 0;
              }
              
            }
                 
			$regular_item=$_REQUEST['regular_item'];
			  if($regular_item=='1'){
				$product_list='';
				foreach ($_REQUEST['product_list'] as $product_list)
				{
					$product_list.=$product_list.',';
				}
				$product_list=rtrim($product_list,",");
				$product_list=strlen($product_list);
				  if($product_list == 0){
					  $foundError = true;
					  $tempErrStep = true;
					  $message .= "<br>&nbsp;&nbsp;&nbsp;- "._CHOOSE_REGULAR_PRODUCT;               
					  $message .= "<br>";
					  $errorSteps[] = 0;
				  }
			  }


        //if($rtn_user->UserCount == 0){  
          $byear = $_REQUEST[$type.'byear'];
          $bmonth = $_REQUEST[$type.'bmonth'];
          $bdate = $_REQUEST[$type.'bdate'];
          $fullBirths = $byear.'-'.$bm.'-'.$bd;
          $koreanName =  $_REQUEST[$type.'kssn_name'];
          if($_REQUEST["skipcancel_rule"]!=1){
            if(!validateKoreanCancellationRules($koreanName,$fullBirths)){
                $foundError = true;
                $tempErrStep = true;
                $message .= "<br>&nbsp;&nbsp;&nbsp;- "._IBO_NOT_ELIBILE_CANCEL_PERIOD;               
                $message .= "<br>";
                $errorSteps[] = 0;
            }
         }
        //}

            //  JIRA ENZ-476 : Disabling the SSN based validations (removed the commented code)
  }
// Validate Step 2: Individual Information
       $tempErrStep = false;
if($vendor_id == "9" || $vendor_id == "17"|| $vendor_id == "18" || $vendor_id == "19" || $vendor_id == "25"){
	if(!isset($_REQUEST['notfn_email_check']) && (!isset($_REQUEST["indemailcheck"]))){
		if (check_email_address($_REQUEST['indemail'])) {
             
        } else {
            $foundError = true;
            $tempErrStep = true;
            $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_EMAIL."<br/>";
            $errorSteps[] = 0;
        }
	}
}

      if($enrolType=="Individual")
                {
                  $type="IND";
                  $country_code=$dbcountry->codes;
                  $countryindividul="REG-".$type."-".$country_code;
                  $sql="select * from #__adv_fields WHERE published = 'yes' and required='y' and enabled_flag
                  LIKE '%".$countryindividul."%' ORDER BY ordering ";
//									echo $sql;
                  $database->setQuery($sql);
                  $fieldname=$database->loadObjectList();
                   $myFieldValue=$_REQUEST["indemail"];


           foreach ($fieldname as $field)
              {


             $fieldName=$field->field_name;


             $requesttype="ind";
             $fieldName=$requesttype."".$fieldName;

             if($field->field_name == 'bday'){
                if(!empty($_REQUEST[$requesttype.'byear']) && !empty($_REQUEST[$requesttype.'bmonth']) && !empty($_REQUEST[$requesttype.'bdate'])){
                  $_REQUEST[$fieldName] = $_REQUEST[$requesttype.'byear'].$_REQUEST[$requesttype.'bmonth'].$_REQUEST[$requesttype.'bdate'];
                }
             }
             if($field->field_name == 'tax_id'){
                if(!empty($_REQUEST['individual_ssn1']) && !empty($_REQUEST['individual_ssn2']) && !empty($_REQUEST['individual_ssn3']) && strlen($_REQUEST['individual_ssn3']) > 6){
                  $_REQUEST[$fieldName] = $_REQUEST['individual_ssn1'].$_REQUEST['individual_ssn2'].$_REQUEST['individual_ssn3'];
                }
             }
if($vendor_id=="10"){
	$_REQUEST['indphone_1']=$_REQUEST['indPhone1'].$_REQUEST['indPhone2'].$_REQUEST['indPhone3'];
	$_REQUEST['indcell_phone']=$_REQUEST['indcell_1'].$_REQUEST['indcell_2'].$_REQUEST['indcell_3'];
}	
             if($_REQUEST[$fieldName]=="")
                {
                $title=convertTitle($field->title);
                 $foundError = true;
                 $tempErrStep = true;

                 $message.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PLEASE_FILL." ".$title."<br/>";
                 $errorSteps[] = 0;
                }
                  }
 if($vendor_id=="10"){
       if(mb_strlen($_REQUEST["indkssn_name"])>14){
            $foundError = true;
            $tempErrStep = true;
            $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_FULL_NAME_MAX_LENGTH."<br/>";
            $errorSteps[] = 0;
       }
 }
            if ((!empty($myFieldValue))&& (empty($_REQUEST["indemailcheck"])))
                {
          if (check_email_address($myFieldValue)) {
             //echo "<BR>Email was good".$myFieldValue;
          } else {
             //echo "<BR>Email was NO good".$myFieldValue;
             $foundError = true;
            $tempErrStep = true;

             $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_EMAIL."<br/>";

             $errorSteps[] = 0;
          }
       }


          if ((!empty($myFieldValue))&& (!empty($_REQUEST["indemailcheck"])))
                {
          if (check_email_address($myFieldValue)) {
             //echo "<BR>Email was good".$myFieldValue;

             $foundError = true;
            $tempErrStep = true;

             $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_EMAIL_CHECKBOX_CHECKED."<br/>";

             $errorSteps[] = 0;

          }
       }

      if(!empty($_REQUEST["indtax_id"]))
      {

      if(!(validateRfc($_REQUEST["indtax_id"],$vendor_id,'ind')))
        {
          $foundError = true;
            $tempErrStep = true;
      $message.= "<br>&nbsp;&nbsp;&nbsp;-". _NEW_REGISTRATION_WRONG_RFC. "<br/>";

          }
        }

      if(($_REQUEST["indis_trained"]==1)){
        if(empty($_REQUEST["indtrained_date"])){
          $foundError = true;
          $tempErrStep = true;
          $message.= "<br>&nbsp;&nbsp;&nbsp;- "._TRAINED_DATE_MISSING." <br/>";          $message.= "<br>&nbsp;&nbsp;&nbsp;- Enter trained date <br/>";
        }
      }

      if(!empty($_REQUEST["indtrained_date"])){
        $splitdate = explode("-",$_REQUEST["indtrained_date"]);
        if(($_REQUEST["indis_trained"]!=1)){
          $foundError = true;
          $tempErrStep = true;
          $message.= "<br>&nbsp;&nbsp;&nbsp;- "._TRAINED_WITH_TRAINED_DATE."<br/>";
        }
        if ((checkdate($splitdate[1],$splitdate[2],$splitdate[0])===false) ||
        (!preg_match("/([0-9]{4})-([0-9]{2})-([0-9]{2})/",$_REQUEST["indtrained_date"]))){
          $foundError = true;
          $tempErrStep = true;
          $message.= "<br>&nbsp;&nbsp;&nbsp;-"._TRAINED_IBO.": "._MY_ISHOP_COUPON_ERROE_MESSAGES15."<br/>";
        }
      }

      if(!empty($_REQUEST["indcurp"]) && $vendor_id=="1")
      {

      if(!(validateCurp($_REQUEST["indcurp"])))
        {
          $foundError = true;
            $tempErrStep = true;
      $message.= "<br>&nbsp;&nbsp;&nbsp;- Wrong CURP<br/>";

          }
        }
            if(!empty($_REQUEST[indpassword]))
                {
                 require_once( $mosConfig_absolute_path. "/administrator/components/com_phpshop/classes/ps_password.php");
                  $ps_password = new ps_password();                  
                if($ps_password->validatePasswordRegex($_REQUEST[indpassword]) != true || $ps_password->validatePasswordRegex($_REQUEST[indconfirmpassword]) != true){
                  $foundError = true;
                  $tempErrStep = true;
                  $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_JAVASCRIPT_ERRORS_PASSWORDFORMAT."<br/>";
                  $errorSteps[] = 0;
                } else if($ps_password->isDictionaryword($_REQUEST[indpassword]) == true) {
                  $foundError = true;
                  $tempErrStep = true;
                  $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_JAVASCRIPT_ERRORS_PASSWORD_DICTIONARY."<br/>";
                  $errorSteps[] = 0;
                }
                if($_REQUEST[indpassword]!=$_REQUEST[indconfirmpassword])
                {
                  $foundError = true;
            $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PASSWORD."<br/>";
             $errorSteps[] = 0;
                }
                }

      //Ne conditions added for SSN

      $ssn=$_REQUEST[individual_ssn1].$_REQUEST[individual_ssn2].$_REQUEST[individual_ssn3];
      $ssnchdck="SELECT COUNT(tax_id) as counts FROM mambophil_adv_users where tax_id=".$ssn;
                  $database->setQuery($ssnchdck);
                  $database->loadObject($ssnchdckresult);
                  $resultssncount=$ssnchdckresult->counts;

                 if(!empty($resultssncount)){
                     $foundError = true;
                 $tempErrStep = true;

                 $message.="<br>&nbsp;&nbsp;&nbsp;- "._REG_ERR_SSN_EXIST."<br/>";
                 $errorSteps[] = 0;

                 }

 if(strlen($ssn)>0 && strlen($ssn)<9)
            {
                 $foundError = true;
                 $tempErrStep = true;
                 if($vendor_id=="9"){
                 $message.="<br>&nbsp;&nbsp;&nbsp;- Please Enter 9 Digits For SSN<br/>";
                 }
                 else if($vendor_id=="17"){
                 $message.="<br>&nbsp;&nbsp;&nbsp;- Please Enter 9 Digits For SIN<br/>";
                 }
                 $errorSteps[] = 0;

            }
      //End of SSN

      // ZIP validation

        if($vendor_id=="17"){
          $billingzip= $_REQUEST[bill_zip1].$_REQUEST[bill_zip2];
          //$shipZip= $_REQUEST[ship_zip1].$_REQUEST[ship_zip2];
          if(strlen($billingzip)>0&& strlen($billingzip)<6){
             $foundError = true;
             $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;-"._NEWREGISTRATION_ERROR_BILLZIP_VALIDATE." <br/>";
             $errorSteps[] = 0;
          }
//           if(strlen($shipZip)>0&& strlen($shipZip)<6){
//              $foundError = true;
//              $tempErrStep = true;
//              $message.= "<br>&nbsp;&nbsp;&nbsp;-"._NEWREGISTRATION_ERROR_SHIPZIP_VALIDATE." <br/>";
//              $errorSteps[] = 0;
//           }

        }

        if($vendor_id=="17"){
  if($_REQUEST["shippingaddress"]=="newship"){
          $shipZip= $_REQUEST[ship_zip1].$_REQUEST[ship_zip2];

          if(strlen($shipZip)>0&& strlen($shipZip)<6){
             $foundError = true;
             $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;-"._NEWREGISTRATION_ERROR_SHIPZIP_VALIDATE." <br/>";
             $errorSteps[] = 0;
          }
          }
        }


      // ZIP validation

      //Phone validation
      if($vendor_id=="9" || $vendor_id=="17" || $vendor_id=="10"){
                     $indphone= $_REQUEST[indPhone1].$_REQUEST[indPhone2].$_REQUEST[indPhone3];
                     }else{
                        $indphone= $_REQUEST[indphone_1];
                      }
               if(strlen($indphone)>0 && strlen($indphone)<10 && ($vendor_id!="12" && $vendor_id!="10" && $vendor_id!="23"))
                {

             $foundError = true;
             $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PHONE_VALIDATE."<br/>";
             $errorSteps[] = 0;

                }
             //for korea. Validation for SLV and GTM is done with some other mantis branch. May get conflict.
             if(strlen($indphone)>0 && $vendor_id=="10"){
               $format = korea_phone_validate($_REQUEST[indPhone1],$_REQUEST[indPhone2],$_REQUEST[indPhone3]);
               if($format === false){
                 $foundError = true;
                 $tempErrStep = true;
                 $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PHONE_VALIDATE_KOR."<br/>";
                 $errorSteps[] = 0;
               }
             }

             if(strlen($indphone)>0 && strlen($indphone)<9 && strlen($indphone)>10 && ($vendor_id=="12" || $vendor_id == "23")) {

               $foundError = true;
               $tempErrStep = true;
               $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PHONE_VALIDATE."<br/>";
               $errorSteps[] = 0;
             }

            if($vendor_id=="9" || $vendor_id=="10"){
                     $indi_cellphone= $_REQUEST[indcell_1].$_REQUEST[indcell_2].$_REQUEST[indcell_3];
                     //$cel = $vendor_id==9 ? 10 : 11;
                        $cel = 10;
                     }else{
                        $indi_cellphone= $_REQUEST[indcell_phone];
                        $cel = 10;
                      }
               if(strlen($indi_cellphone)>0&& strlen($indi_cellphone)<$cel && $vendor_id!="10" && $vendor_id != 23)
                {

                 $foundError = true;
                 $tempErrStep = true;
                 $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CELLPHONE_VALIDATE."<br/>";
                 $errorSteps[] = 0;

                }
                if(strlen($indi_cellphone)>0 && strlen($indi_cellphone)<9 && strlen($indi_cellphone)>10 && $vendor_id == "23")
                {
                 $foundError = true;
                 $tempErrStep = true;
                 $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CELLPHONE_VALIDATE."<br/>";
                 $errorSteps[] = 0;
                }
                
             if($vendor_id=="10"){  
             $indcellPhone = $_REQUEST[indcell_1].$_REQUEST[indcell_2].$_REQUEST[indcell_3];
             if(strlen($indcellPhone)>0) {
               $format = korea_cell_validate($_REQUEST[indcell_1],$_REQUEST[indcell_2],$_REQUEST[indcell_3]);
               if($format === false){
                 $foundError = true;
                 $tempErrStep = true;
                 $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CELLPHONE_VALIDATE_KOREA."<br/>";
                 $errorSteps[] = 0;
               }
               }
             } 

      //phone validation

      if(!empty($_REQUEST["nickname"])){
       $nickname=trim($_REQUEST["nickname"]);

       $sqlUSR = "SELECT count(id) as cnt FROM  mambophil_users WHERE name='".$nickname."'";
                    $database->setQuery($sqlUSR);
                    $database->loadObject($resUSR);
       $count=$resUSR->cnt;
       if($count > 0 ){

           $foundError = true;
           $tempErrStep = true;
           $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NICK_NAME_NOTVALID."<br/>";
           $errorSteps[] = 0;

            }
        $domain = strstr($nickname, ' ');
        if($domain){
         $space = 1;
        }else{
         $space = 0;
        }

        if($space==1){
           $foundError = true;
           $tempErrStep = true;
           $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NICK_NAME_NOTVALID_NEW_REGISTRATION."<br/>";
           $errorSteps[] = 0;
        }

       }else{

            $foundError = true;
           $tempErrStep = true;
           $message.= "<br>&nbsp;&nbsp;&nbsp;"._NICK_NAME_NOT_ENTERED."<br/>";
           $errorSteps[] = 0;

       }

              if($tempErrStep)
             {
             $message = "<h3> "._NEWREGISTRATION_ERROR_INDIVIDUAL." </h3>".$message;
             }
           }
    //End of Validate Step 2: Individual Information
    // Validate Step 2:  Business Information
        else
         {
                  $type="BUS";
                  $country_code=$dbcountry->codes;
                  $countrybusiness="REG-".$type."-".$country_code;
                  $sql="select * from #__adv_fields WHERE published = 'yes' and required='y' and enabled_flag
                  LIKE '%".$countrybusiness."%' ORDER BY ordering ";
                  $database->setQuery($sql);
                  $fieldname=$database->loadObjectList();
                  $myFieldValue=$_REQUEST["busemail"];
           foreach ($fieldname as $field)
              {
             $fieldName=$field->field_name;

             $requesttype="bus";
             $fieldName=$requesttype."".$fieldName;

             if($field->field_name == 'bday'){
                if(!empty($_REQUEST[$requesttype.'byear']) && !empty($_REQUEST[$requesttype.'bmonth']) && !empty($_REQUEST[$requesttype.'bdate'])){
                  $_REQUEST[$fieldName] = $_REQUEST[$requesttype.'byear'].$_REQUEST[$requesttype.'bmonth'].$_REQUEST[$requesttype.'bdate'];
                }
             }
             if($vendor_id=="10"){
	$_REQUEST['busphone_1']=$_REQUEST['busPhone1'].$_REQUEST['busPhone2'].$_REQUEST['busPhone3'];
	$_REQUEST['buscell_phone']=$_REQUEST['buscell_1'].$_REQUEST['buscell_2'].$_REQUEST['buscell_3'];
}
             if($_REQUEST[$fieldName]=="")
                {
                $title=convertTitle($field->title);
                 $foundError = true;
                 $tempErrStep = true;
                 $message.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PLEASE_FILL." ".$title."<br/>";
                 $errorSteps[] = 0;
                }
                }


                 if($vendor_id=="17"){
                              if($_REQUEST["shippingaddress"]=="newship"){
                $shipZip= $_REQUEST[ship_zip1].$_REQUEST[ship_zip2];
                if(strlen($shipZip)>0&& strlen($shipZip)<6)
                {

             $foundError = true;
             $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;-"._NEWREGISTRATION_ERROR_SHIPZIP_VALIDATE." <br/>";
             $errorSteps[] = 0;

                }
               }
            }    // ZIP validation

        if($vendor_id=="17"){
                     $billingzip= $_REQUEST[bill_zip1].$_REQUEST[bill_zip2];

               if(strlen($billingzip)>0&& strlen($billingzip)<6)
                {

             $foundError = true;
             $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;-"._NEWREGISTRATION_ERROR_BILLZIP_VALIDATE." <br/>";
             $errorSteps[] = 0;

                }


             }
      // ZIP validation




                if ((!empty($myFieldValue))&& (empty($_REQUEST["busemailcheck"])))
                {
          if (check_email_address($myFieldValue)) {
             //echo "<BR>Email was good".$myFieldValue;
          } else {
             //echo "<BR>Email was NO good".$myFieldValue;
             $foundError = true;
            $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_EMAIL."<br/>";
             $errorSteps[] = 0;
          }
       }

       if(($_REQUEST["busis_trained"]==1)){
        if(empty($_REQUEST["bustrained_date"])){
          $foundError = true;
          $tempErrStep = true;
          $message.= "<br>&nbsp;&nbsp;&nbsp;- "._TRAINED_DATE_MISSING." <br/>";
        }
      }

      if(!empty($_REQUEST["bustrained_date"])){
        $splitdate = explode("-",$_REQUEST["bustrained_date"]);
        if(($_REQUEST["busis_trained"]!=1)){
          $foundError = true;
          $tempErrStep = true;
          $message.= "<br>&nbsp;&nbsp;&nbsp;- "._TRAINED_WITH_TRAINED_DATE."<br/>";
        }
        if ((checkdate($splitdate[1],$splitdate[2],$splitdate[0])===false) ||
        (!preg_match("/([0-9]{4})\-([0-9]{2})\-([0-9]{2})/",$_REQUEST["bustrained_date"]))){
          $foundError = true;
          $tempErrStep = true;
          $message.= "<br>&nbsp;&nbsp;&nbsp;-"._TRAINED_IBO.": "._MY_ISHOP_COUPON_ERROE_MESSAGES15."<br/>";
        }
      }


       if ((!empty($myFieldValue))&& (!empty($_REQUEST["busemailcheck"])))
                {
          if (check_email_address($myFieldValue)) {
             //echo "<BR>Email was good".$myFieldValue;

             $foundError = true;
            $tempErrStep = true;

             $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_EMAIL_CHECKBOX_CHECKED."<br/>";

             $errorSteps[] = 0;

          }
       }

            if(!empty($_REQUEST[buspassword]))
                {
                  require_once( $mosConfig_absolute_path. "/administrator/components/com_phpshop/classes/ps_password.php");
                  $ps_password = new ps_password();   
                if($ps_password->validatePasswordRegex($_REQUEST[buspassword]) != true || $ps_password->validatePasswordRegex($_REQUEST[busconfirmpassword]) != true){
                  $foundError = true;
                  $tempErrStep = true;
                  $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_JAVASCRIPT_ERRORS_PASSWORDFORMAT."<br/>";
                  $errorSteps[] = 0;
                }else if($ps_password->isDictionaryword($_REQUEST[buspassword]) == true) {
                  $foundError = true;
                  $tempErrStep = true;
                  $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_JAVASCRIPT_ERRORS_PASSWORD_DICTIONARY."<br/>";
                  $errorSteps[] = 0;
                } 
                if($_REQUEST[buspassword]!=$_REQUEST[busconfirmpassword])
                {
                  $foundError = true;
            $tempErrStep = true;
             $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PASSWORD."<br/>";
             $errorSteps[] = 0;
                }
                }
      //Business tax ID validation
      if($vendor_id=="9"){
      $business_taxid=$_REQUEST["business_taxid1"].$_REQUEST["business_taxid2"];

      if(strlen($business_taxid)>0 && strlen($business_taxid)<9)
            {
                 $foundError = true;
                 $tempErrStep = true;

                 $message.="<br>&nbsp;&nbsp;&nbsp;- Please Enter 9 Digits For Business Tax Id<br/>";
                 $errorSteps[] = 0;

            }
            }

            else{

             if(!empty($_REQUEST["bustax_id"]))
      {
        // $bus is a flag to check enrollment type. If business then true
      if(!(validateRfc($_REQUEST["bustax_id"],$vendor_id,'bus')))
        {
          $foundError = true;
            $tempErrStep = true;
      $message.= "<br>&nbsp;&nbsp;&nbsp;-". _NEW_REGISTRATION_WRONG_RFC. "<br/>";

          }
        }

         }

      if(strlen($_REQUEST["bustax_id"]) != 8 && $vendor_id == "23"){
        $foundError = true;
        $tempErrStep = true;
        $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_BUSSINESS_ID."<br/>";
        $errorSteps[] = 0;
      }

      if($vendor_id=="9" || $vendor_id=="17" || $vendor_id=="10"){
                      $busphone=$_REQUEST[busPhone1].$_REQUEST[busPhone2].$_REQUEST[busPhone3];
                      }else{
                        $busphone=$_REQUEST[busphone_1];
                     }
                  if(strlen($busphone)>0&& strlen($busphone)<10 && ($vendor_id!="12" && $vendor_id!="10" && $vendor_id != "23")){
                        $foundError = true;
                        $tempErrStep = true;
                        $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PHONE_VALIDATE."<br/>";
                        $errorSteps[] = 0;
                }

                  if(strlen($indphone)>0 && strlen($indphone)<9 && strlen($indphone)>10 && ($vendor_id=="12" || $vendor_id == "23")) {

                       $foundError = true;
                       $tempErrStep = true;
                       $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PHONE_VALIDATE."<br/>";
                       $errorSteps[] = 0;
             }

             //for korea. Validation for SLV and GTM is done with some other mantis branch. May get conflict.
             if(strlen($busphone)>0 && $vendor_id=="10"){

               $format = korea_phone_validate($_REQUEST[busPhone1],$_REQUEST[busPhone2],$_REQUEST[busPhone3]);
               if($format === false){
                 $foundError = true;
                 $tempErrStep = true;
                 $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_PHONE_VALIDATE_KOR."<br/>";
                 $errorSteps[] = 0;
               }
             }

                   if($vendor_id=="9" || $vendor_id=="10"){
                      $busi_cellphone=$_REQUEST[buscell_1].$_REQUEST[buscell_2].$_REQUEST[buscell_3];
                      }else{
                        $busi_cellphone=$_REQUEST[buscell_phone];
                     }
                  if(strlen($busi_cellphone)>0&& strlen($busi_cellphone)<10 && $vendor_id!="10" && $vendor_id != 23){
                    if($vendor_id=="10"){
                      $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CELLPHONE_VALIDATE_KOREA."<br/>";
                    }
                    else{
                      $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CELLPHONE_VALIDATE."<br/>";
                    }
                  $foundError = true;
                  $tempErrStep = true;
                  $errorSteps[] = 0;
                }

                if(strlen($busi_cellphone)>0 && strlen($busi_cellphone)<9 && strlen($busi_cellphone)>10 && $vendor_id == "23")
                {
                 $foundError = true;
                 $tempErrStep = true;
                 $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CELLPHONE_VALIDATE."<br/>";
                 $errorSteps[] = 0;
                }

                if($vendor_id=="10"){
                $buscellPhone = $_REQUEST[buscell_1].$_REQUEST[buscell_2].$_REQUEST[buscell_3];
                if(strlen($buscellPhone)>0) {
                  $format = korea_cell_validate($_REQUEST[buscell_1],$_REQUEST[buscell_2],$_REQUEST[buscell_3]);
                  if($format === false){
                    $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CELLPHONE_VALIDATE_KOREA."<br/>";
                    $foundError = true;
                    $tempErrStep = true;
                    $errorSteps[] = 0;
                  }
                  }
                }

                 if(!empty($_REQUEST["nicknamebusiness"])){
       $nicknamebusiness=$_REQUEST["nicknamebusiness"];

       $sqlUSR = "SELECT count(id) as cnt FROM  mambophil_users WHERE name='".$nicknamebusiness."'";
                    $database->setQuery($sqlUSR);
                    $database->loadObject($resUSR);
       $count=$resUSR->cnt;
       if($count > 0 ){

           $foundError = true;
           $tempErrStep = true;
           $message.= "<br>&nbsp;&nbsp;&nbsp;- "._NICK_NAME_NOTVALID."<br/>";
           $errorSteps[] = 0;

            }
       }else{

            $foundError = true;
           $tempErrStep = true;
           $message.= "<br>&nbsp;&nbsp;&nbsp;"._NICK_NAME_NOT_ENTERED."<br/>";
           $errorSteps[] = 0;

       }

              if($tempErrStep)
             {
             $message= "<h3>"._NEWREGISTRATION_ERROR_BUSINESS_INFORMATION." </h3>".$message;
             }
          }
    if($_REQUEST["placement_opt"]=="yes"){
      if($_REQUEST[tree_placement]==""){
        $foundError = true;
        $tempErrStep = true;
        $message.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_TREE_PLACEMENT."<br/>";
        $errorSteps[] = 0;
      }
      // Sponsor Validation
      if(empty($_REQUEST["ibo_sponsor"])){
        $foundError = true;
        $tempErrStep = true;
        $message.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_TREE_PLACEMENT_SPONOSR."<br/>";
        $errorSteps[] = 0;
      }else{
        // Validate The sponsor
        $tree_ibo = $_REQUEST["tree_placement"];
        $tree_chunks = explode(' ', $tree_ibo, 2);
        $tree_ibo = trim($tree_chunks[0]);
        $sponsor_number = $_REQUEST["ibo_sponsor"];

      	require_once( $GLOBALS['mosConfig_absolute_path'] . '/components/com_ibo/ibofunctions.php' );
        if (ValidateSponsorInUpline($tree_ibo, $sponsor_number) == false) {
          $foundError = true;
          $tempErrStep = true;
          $message.="<br>&nbsp;&nbsp;&nbsp;- "._SPONSOR_SELECT."<br/>";
          $errorSteps[] = 0;
      	}
      }

      if($vendor_id=="1"){
        $ibocloser = trim($_REQUEST["ibo_closer"]);
        $ibosponsor = trim($_REQUEST["ibo_sponsor"]);
        if(!empty($ibocloser)){
           if($ibocloser == $ibosponsor){
              $foundError = true;
              $tempErrStep = true;
              $message.="<br>&nbsp;&nbsp;&nbsp;- "._CLOSER_SPONSOR_ERROR."<br/>";
              $errorSteps[] = 0;
          }

        $closercount="SELECT COUNT(u.username)AS counts FROM mambophil_users u
						INNER JOIN mambophil_adv_users au ON u.id = au.id AND au.status_cd = 'A'
						WHERE u.username='".$ibocloser."'";
  			$database->setQuery($closercount);
  			$database ->loadObject($dbcloser);
  			$closercount=$dbcloser->counts;
  			if(empty($closercount))
  			{
  				 $foundError = true;
      		     $tempErrStep = true;
  	        	 $message.="<br>&nbsp;&nbsp;&nbsp;- "._CLOSER_INVALID_ERROR."<br/>";
  		         $errorSteps[] = 0;
  			}elseif (ValidateSponsorInUpline($tree_ibo, $ibocloser) === false) {
          $foundError = true;
          $tempErrStep = true;
          $message.="<br>&nbsp;&nbsp;&nbsp;- "._CLOSER_SELECT."<br/>";
          $errorSteps[] = 0;
      	}
  		}

    }

      $sponsorcount="SELECT COUNT(username)as counts from mambophil_users where username='".$_REQUEST["ibo_sponsor"]."'";
      $database->setQuery($sponsorcount );
      $database ->loadObject($dbsponsor);
      $check_sponosr=$dbsponsor->counts;

      if(empty($check_sponosr)){
         $foundError = true;
         $tempErrStep = true;
         $message.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_TREE_PLACEMENT_VALID."<br/>";
         $errorSteps[] = 0;
      }
    }

      if($_REQUEST["pibo"]=="Y" && $_REQUEST["contryid"]=="83"){
    //End Of Validate Step 2:  Business Information
    if($_REQUEST["korean_public_tree_not_flag"]=="1" || $_REQUEST["korean_public_tree_flag"]!="1"){
         $foundError = true;
         $tempErrStep = true;
         $message.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_TREE_PLACEMENT."<br/>";
         $errorSteps[] = 0;
    }
  }

      //preferred customer upgrade validation starts
   $preferredcustomer_num=mosGetParam( $_REQUEST, 'pc_number', '');
  if($_REQUEST['preferredcustomer']){
              $sql = "SELECT COUNT(u.`id`) as counts, u.`username` FROM mambophil_users u
              INNER JOIN mambophil_adv_users au on au.id=u.id
                       WHERE
                      usertype = 'Enzacta ABC Member' AND au.status_cd='A' AND u.username='".$preferredcustomer_num."'";
    $database->setQuery($sql);
    $database->loadObject( $pref_customer );

    if($pref_customer->counts == 0){
      $foundError = true;
      $tempErrStep = true;
      $message.="<br>&nbsp;&nbsp;&nbsp;- Please Enter a Valid Personal Customer Number<br/>";
     $errorSteps[] = 0;
    }
   } //preferred customer validation ends.


  // Validate Step 8: Billing
  $tempmessage="";
  $tempErrStep = false;
  $billadd1=$_REQUEST[billingaddress1];
  $shipadd1=$_REQUEST[shippingaddress1];
  $billadd2=$_REQUEST[billingaddress2];
  $shipadd2=$_REQUEST[shippingaddress2];

 //validating the billing address:

    if($billadd1==""){
      $foundError = true;
      $tempErrStep = true;
      $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_BILLING_ADDRESS1."<br/>";
      $errorSteps[] = 2;
    }
    if($vendor_id==10){ //additional address2 validation for korea (Jira ENZ-2435)
     if($billadd2==""){
      $foundError = true;
      $tempErrStep = true;
      $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_BILLING_ADDRESS2."<br/>";
      $errorSteps[] = 2;
     }
    }

    if($vendor_id==1){
       $state=$_REQUEST[mexbillstates];
       $city=$_REQUEST[mexbillcityList];
       $area=$_REQUEST[mexbillareaList];
       $zip=$_REQUEST[billzipcodesmex];
    }else{
       $address1=$_REQUEST["billingaddress1"];
       $address2=$_REQUEST["billingaddress2"];
       $city=$_REQUEST["billingcity"];
       $state=$_REQUEST["billingstate"];
    if($vendor_id==17){
     $zip=$_REQUEST["bill_zip1"]." ".$_REQUEST["bill_zip2"];
    }
    else
    {
     $zip=$_REQUEST["billingzip"];
    }
    }

    //ups validation starts
      if($country_code=="USA"){
       //$flag1= validateaddress($city,$state,$zip,'US');
       /*
       if($flag1==0){
         $var_err=1;
         $foundError = true;
         $tempErrStep = true;
         $tempmessage.="<br>&nbsp;&nbsp;&nbsp;-&nbsp;"._NEWREGISTRATION_ERROR_BILLING_MATCH."<br/>";
         $errorSteps[] = 2;
       }*/
      //ups validation ends
   }else if($country_code=="CAN"){
      $res = ValidateCanadaAddress($address1, $address2, '', $city, $state, $zip, 'CAN');
      if($res !== true){
        $var_err=1;
        $foundError = true;
        $tempErrStep = true;
        $tempmessage.="<br>&nbsp;&nbsp;&nbsp;-&nbsp;"._NEWREGISTRATION_ERROR_BILLING_MATCH."<br/>";
        $errorSteps[] = 2;
      }
   }else if($country_code=="KOR"){
     if($zip==""){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage.="<br>&nbsp;&nbsp;&nbsp;-&nbsp;"._NEWREGISTRATION_JAVASCRIPT_ERRORS_BILLING_ZIP."<br/>";
       $errorSteps[] = 2;
     }
   }else if($country_code=="TWN" || $country_code=="HKG"){
     if(/*(removed billing country chooser)!isset($_REQUEST["country_chooser_billing_address"]) && */$state==""){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage.="<br>&nbsp;&nbsp;&nbsp;-&nbsp;"._NEWREGISTRATION_ERROR_BILLING_MATCH."("._NEWREGISTRATION_STATE.")<br/>";
       $errorSteps[] = 2;
     }elseif($zip==""){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage.="<br>&nbsp;&nbsp;&nbsp;-&nbsp;"._NEWREGISTRATION_ERROR_BILLING_MATCH."("._NEWREGISTRATION_ZIP.")<br/>";
       $errorSteps[] = 2;
	 }
   }
   else{
     if(($city=="")||($state=="")||($zip=="")){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage.="<br>&nbsp;&nbsp;&nbsp;-&nbsp;"._NEWREGISTRATION_ERROR_BILLING_MATCH."<br/>";
       $errorSteps[] = 2;
     }
   }
 //end of validatin billing addrsss
  if($tempErrStep)
          {
            $message.= "<h3>"._NEWREGISTRATION_ERROR_ADRESS_BILLING."</h3>".$tempmessage;
          }


  if($_REQUEST["autoaward"]=="0000"){
      $noitemflag=true;
  }
  if(!$noitemflag){
   //Validate step 3 Bunisess Starter kit
  if($vendor_id!="10" && $vendor_id != 23 && $vendor_id != 26 && $vendor_id != 27){// same condition applying for validation here from 172 line  ajax_new_resitration.php
   $tempErrStep = false;
    $tempmessage="";
    $Kit=$_REQUEST["starterkit"];
    if($Kit=="")
    {
      $foundError = true;
      $tempErrStep = true;
      $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_BUSINESS_STARTRER_KIT."<br/>";
      $errorSteps[] = 7;
    }
    if($tempErrStep)
             {
             $message.= "<h3>"._NEWREGISTRATION_ERROR_BUSINESS_KIT_PLAN." </h3>".$tempmessage;
             }
   }
   //End of validate Business Starter kit


    // Validate Step 4: Join Plan
    $tempErrStep = false;
    $tempmessage="";
    $join=$_REQUEST["joinproductradio"];
    $regular_item = $_REQUEST["regular_item"];

    if($join=="" && $regular_item != 1)
    {
      $foundError = true;
      $tempErrStep = true;
      $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_JOIN_ITEM."<br/>";
      $errorSteps[] = 1;
    }
    if($tempErrStep)
             {
             $message.= "<h3>"._NEWREGISTRATION_ERROR_JOIN_PLAN." </h3>".$tempmessage;
             }
   // End Of Validate Step 4: Join Plan

    //Validate step7:Autoship Selection
   $tempmessage="";
  $tempErrStep = false;
      $autoship= $_REQUEST[showautoship];

       if($autoship=="showautoshi")
       {

        if(($_REQUEST[start_month]=="")||($_REQUEST[eday]=="0"))
          {
          $foundError = true;
          $tempErrStep = true;
          $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_AUTOSHIP." <br/>";
          $errorSteps[] = 2;
          }

         if(empty($_REQUEST["autoproduct_list"]))
          {
          $foundError = true;
          $tempErrStep = true;
          $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_AUTOSHIP_PRODUCT_ERROR." <br/>";
          $errorSteps[] = 2;
          }

		  if($_REQUEST['payments'] == 'fcpay'){
			  $sql = 'SELECT minimum_amount FROM #__pshop_payment_method WHERE payment_method_id = ' . $_REQUEST['fc_paymentmethod_id'];
			  $database->setQuery($sql);
			  $database->loadObject($payment_info);

			  if($payment_info->minimum_amount > 0 ){
				  $autoship_amount = 0.0;
				  foreach($_REQUEST["autoproduct_list"] as $product){
					  list($prod_id, $qty) = explode('|', $product);
					  $sql = 'SELECT (product_price * ' . $qty . ') AS amount FROM #__pshop_product_price WHERE product_id = ' . $prod_id;
					  $database->setQuery($sql);
					  $database->loadObject($prod);
					  $autoship_amount += $prod->amount;
				  }

				  if($payment_info->minimum_amount > $autoship_amount){
					  $foundError = true;
					  $tempErrStep = true;
					  $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_AUTOSHIP_AMOUNT_ERROR." $".round($payment_info->minimum_amount, 2)." <br/>";
					  $errorSteps[] = 2;
				  }
			  }
		  }
       }
       if($tempErrStep)
             {
             $message.= "<h3>"._NEWREGISTRATION_ERROR_AUTOSHIP_STEPS." </h3>".$tempmessage;
             }
   // End of Validate step7:Autoship Selection


  $tempmessage="";
  $tempErrStep = false;
  $billadd1=$_REQUEST[billingaddress1];
  $shipadd1=$_REQUEST[shippingaddress1];
  $shipadd2=$_REQUEST[shippingaddress2];
 //strat of validatinf shipping address
   $ship_rate_id = mosgetparam( $_REQUEST, 'shipping_rate_id', '');
   $no_shipping = mosgetparam( $_REQUEST, 'no_shipping', '');
   if($vendor_id == "10" &&  $no_shipping == 1 ) {
     $ship_rate_id = "" ;
    }else {
     if($_REQUEST["shippingaddress"]=="newship"){

         if($shipadd1=="")
           {
           $foundError = true;
           $tempErrStep = true;
           $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING_ADDRESS1."<br/>";
           $errorSteps[] = 2;
            }
         if($vendor_id==10){ //additional address2 validation for korea (Jira ENZ-2435)
          if($shipadd2=="")
           {
           $foundError = true;
           $tempErrStep = true;
           $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING_ADDRESS2."<br/>";
           $errorSteps[] = 2;
          }
         }
          if($_REQUEST[shipping_first_name]=="")
           {
           $foundError = true;
           $tempErrStep = true;
           $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING_FIRSTNAME."<br/>";
           $errorSteps[] = 2;
            }

    if($vendor_id==1){
      $stateship=$_REQUEST[mexshipstates];
      $cityship=$_REQUEST[mexshipcityList];
      $areaship=$_REQUEST[mexareaList];
      $zipcodeship=$_REQUEST[zipcodesmex];
    }else{
      $address1_ship=$_REQUEST['shippingaddress1'];
      $address2_ship=$_REQUEST['shippingaddress2'];
      $cityship=$_REQUEST['shipcity'];
      $stateship=$_REQUEST['shipstate'];
    if($vendor_id==17){
     $zipcodeship=$_REQUEST["ship_zip1"]." ".$_REQUEST["ship_zip2"];
    }
    else
    {
     $zipcodeship=$_REQUEST["shipzip"];
    }
    }

    if($country_code=="USA"){
    	/*
      $flag1= validateaddress($cityship,$stateship,$zipcodeship,'US');
      if($flag1==0){
        $var_err=1;
        $foundError = true;
        $tempErrStep = true;
        $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING__MATCH."<br/>";
        $errorSteps[] = 2;
      }*/
    //ups validation ends
    }else if($country_code=="CAN"){
      $res = ValidateCanadaAddress($address1_ship, $address2_ship, '', $cityship, $stateship, $zipcodeship, 'CAN');
      if($res !== true){
        $var_err=1;
        $foundError = true;
        $tempErrStep = true;
        $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING__MATCH."<br/>";
        $errorSteps[] = 2;
      }
   }else{
      if($vendor_id != 10){
       if($vendor_id==23 || $vendor_id==26){
        if(($stateship=="")||($zipcodeship=="")){
          $foundError = true;
          $tempErrStep = true;
          $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING__MATCH."<br/>";
          $errorSteps[] = 2;
         }
       }else{
        if(($stateship=="")||($cityship=="")||($zipcodeship=="")){
          $foundError = true;
          $tempErrStep = true;
          $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING__MATCH."<br/>";
          $errorSteps[] = 2;
        }
      }
      }
    }

   // ZIP validation


 }/*elseif($_REQUEST["shippingaddress"]=="sh_as_bill"){
		if($country_code != $_REQUEST["country_chooser_billing_address"]){
	    $foundError = true;
	    $tempErrStep = true;
	    $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- Shipping address cant take Billing address selected country, please set your country of residence on Billing Address or add a new Shipping Address.<br/>";
	    $errorSteps[] = 2;
		}
 
 }*/
      if(empty($ship_rate_id)){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_SHIPPING_METHODS." <br/>";
       $errorSteps[] = 2;
      }
 //end of validation shipping addresss
         if($tempErrStep)
          {
            $message.= "<h3>"._NEWREGISTRATION_ERROR_ADRESS_SHIPPING."</h3>".$tempmessage;
          }

    }
   // End Of Validate Step 8: Shipping


//$order_amount

  
   //Here Goes the New Zealand Shipping Address Validation against API
   
   if($country_code=="NZL"){
   
        if(stripos($ship_rate_id,"Regular")!==false){
            $shipping_regular=true;
        }
  if($shipping_regular){
     if($_REQUEST["shippingaddress"]=="newship"){
           $address1_to_validate=$_REQUEST['shippingaddress1'];
           $address2_to_validate=$_REQUEST['shippingaddress2'];
           $cityship_to_validate=$_REQUEST['shipcity'];
           $stateship_to_validate=$_REQUEST['shipstate'];
           $zip_to_validate=$_REQUEST['shipzip'];
     }else{
           $address1_to_validate=$_REQUEST['billingaddress1'];
           $address2_to_validate=$_REQUEST['billingaddress2'];
           $cityship_to_validate=$_REQUEST['billingcity'];
           $stateship_to_validate=$_REQUEST['billingstate'];
           $zip_to_validate=$_REQUEST['billingzip'];
     }
   
          $addressDetails=array("Address1"=>$address1_to_validate,"Address2"=>$address2_to_validate,"city"=>$cityship_to_validate,"zip"=>$zip_to_validate);
          require_once(CLASSPATH."warehouse/fastway/warehousemanager.php");
          $warehouse =new WarehouseManager(); 
          $warehouse->ValidateAddressandFranchiseOnFastWay($addressDetails);
          if($warehouse->isServicesAvailable!=1){
            $foundError = true;
            $tempErrStep = true;
            $tempmessage.="<br>&nbsp;&nbsp;&nbsp;- ".$PHPSHOP_LANG->_PHPSHOP_FASTWAY_WAREHOUSE_SHIPPING_MESSAGE." <br/>";
            $errorSteps[] = 2;
            if($tempErrStep)
               {
               $message.= "<h3>"._NEWREGISTRATION_ERROR_ADRESS_SHIPPING."</h3>".$tempmessage;
             }
          }
      }
   } 
   
   //End of New Zealand Shipping address vlaidation against API
  
  

   // Validate Step 9: Payment Information
   $tempmessage="";
   $tempErrStep = false;
   $payment=$_REQUEST[payments];


   $username=$_REQUEST["maxUserId"];
   $SQL_TEMP_PAYMNET="SELECT COUNT(pid) as counts FROM mambophil_pshop_registration_temp_multiple_payment where username='".$username."'";
                         $database->setQuery($SQL_TEMP_PAYMNET);
                          $database->loadObject($rowcount);
                          $counts=$rowcount->counts;
      if((!empty($counts))){
      $remaining="SELECT SUM(order_total) as tot FROM mambophil_pshop_registration_temp_multiple_payment WHERE username='".$username."' GROUP BY username";
                         $database->setQuery($remaining);
                          $database->loadObject($row1);
                          $addedamount=$row1->tot;

                        #$order_amount=trim($order_amount);
                        #$addedamount=trim($addedamount);
                        $order_amount=round(floatval($order_amount),2);
                        $addedamount=round(floatval($addedamount),2);

                        //check for single payment and add payment button is clicked
//                         $multiautoerror = false;
//                         if ($counts==1 && ($order_amount==$addedamount)){
//                           $multiautoerror = true;
//                         }

          if($order_amount!=$addedamount){

                   $foundError = true;
                     $tempErrStep = true;
                     $message.="<br>&nbsp;&nbsp;&nbsp;-"._NEWREGISTRATION_ADD_MULTILE_PAYMENT_AMOUNT_MISMACH."<br/>";
                     $errorSteps[] = 3;
                   }

          $AUTOSHIP_JOINS=$_REQUEST["showautoship"];
           $joinplans=explode("-",$join);
  //echo $joinplans[1];
//      if($AUTOSHIP_JOINS=="showautoshi" && !$multiautoerror) {
//                      $foundError = true;
//                      $tempErrStep = true;
//                      $tempmessage="<br>&nbsp;&nbsp;&nbsp;-"._NEWREGISTRATION_ADD_MULTILE_PAYMENT_AUOSHIP_ERROR."<br/>";
//                      $errorSteps[] = 3;
//
//         }
   }else{
    
  //Validation for steep 6 creditcard's terms and condition
  $terms_cond_payment_cc = $_REQUEST["chk_cc"];
  $terms_cond_payment_fc = $_REQUEST["chk_fc"];
  $terms_cond_payment_db = $_REQUEST["chk_db"];
  if ($payment == "ccpay" || $payment == "kiccpay" ) {
    if ($terms_cond_payment_cc != "1") {
      $message.= "<br>&nbsp;&nbsp;&nbsp;- Step 6: "._NEWREGISTRATION_ERROR_TERMSCONDITIONS."</br>";
      $foundError = true;
    }
  }else if ($payment == "dbpay") {
    if ($terms_cond_payment_db != "1") {
      $message.= "<br>&nbsp;&nbsp;&nbsp;- Step 6: "._NEWREGISTRATION_ERROR_TERMSCONDITIONS."</br>";
      $foundError = true;
    }
  }else if ($payment == "fcpay" || $payment == "easyswipe") { 
    if ($terms_cond_payment_fc != "1") {
      $message.= "<br>&nbsp;&nbsp;&nbsp;- Step 6: "._NEWREGISTRATION_ERROR_TERMSCONDITIONS."</br>";
      $foundError = true;
    }
  }

   $AUTOSHIP_JOINS=$_REQUEST["showautoship"];
    if($payment=="ccpay")
        {
      if($vendor_id != "10" ) {  //Korean payment validation disable starts
          $payment_method_id = mosgetparam( $_REQUEST, 'cc_paymentmethod_id', 0);
          $order_payment_name = mosgetparam( $_REQUEST, 'order_payment_name', 0);
          $order_payment_number1 = mosgetparam( $_REQUEST, 'order_payment_number1', 0);
          $order_payment_number2 = mosgetparam( $_REQUEST, 'order_payment_number2', 0);
          $order_payment_number3 = mosgetparam( $_REQUEST, 'order_payment_number3', 0);
          $order_payment_number4 = mosgetparam( $_REQUEST, 'order_payment_number4', 0);
          $credit_card_code = mosgetparam( $_REQUEST, 'credit_card_code', 0);
          $creditcard_code = mosgetparam( $_REQUEST, 'creditcard_code', 0);
          $order_payment_expire_month = mosgetparam( $_REQUEST, 'order_payment_expire_month', 0);
          $order_payment_expire_year = mosgetparam( $_REQUEST, 'order_payment_expire_year', 0);
          $billing_info_id=mosgetparam( $_REQUEST, 'billing_info_id', '0');

          if($vendor_id == "15" ){
            $cuotas = mosgetparam( $_REQUEST, 'cuotas', 0);
            if(empty($cuotas)|| $cuotas<1 || $cuotas>48){
              $foundError = true;
              $tempErrStep = true;
              $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._CUOTAS_ERROR." <br/>";
              $errorSteps[] = 3;
            }
          }

          $_REQUEST["billing_info_id"]=$_REQUEST['maxUserId'];
          $_REQUEST["payment_method_id"]=$payment_method_id;
          $order_payment_number = $order_payment_number1.$order_payment_number2.$order_payment_number3.$order_payment_number4;


    if(((empty($order_payment_number4))||(empty($order_payment_number2))||(!isset($order_payment_number4)))&&($creditcard_code!="amex") && $payment_method_id!=140 ){
     //echo "<span class='errorwarning'>"._AUTOSHIPNEW_DESCRIPTION_ERROR."</span>";

       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR1." <br/>";
       $errorSteps[] = 3;

      }else if( ( (empty($order_payment_number1))||(!isset($order_payment_number1)) ) && $payment_method_id!=140 ){

       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR2."<br/>";
       $errorSteps[] = 3;

      }elseif ( ( ($order_payment_name == '') || ($order_payment_number1 == '' && $order_payment_number2 == '' && $order_payment_number3 == '' && $order_payment_number4 == '')||($credit_card_code == '')) && $payment_method_id!=140){

      $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR3." <br/>";
       $errorSteps[] = 3;
      }else {


      $d = $_REQUEST;
      $result = $ps_checkout->validate_payment_method(&$d, false);
      if ( !$result) {
      //echo $order_payment_number;



      $errorMessage = $d["error"];
      //formating the error message display
      $errorMessage1=str_replace("<tr><td spancols=3 class='errorwarning'><tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<BR><span class='errorwarning'>",$errorMessage);
      $errorMessage=str_replace("</td>  <td width='15%'class='errorwarning'>&nbsp;</td></tr>","</span><BR>",$errorMessage1);
      $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$errorMessage);
      $errorMessage1="<input type=\"hidden\" name=\"vpayment\" id=\"vpayment\" value=\"0\" /><table class='errorwarning'> ". $errorMessage1 . "</table>";
      $var_err=1;
       $foundError = true;
       $tempErrStep = true;
       $tempmessage=$errorMessage1;
       $errorSteps[] = 3;

       }
     }
    } //Korean payment validation disable ends
              if(($AUTOSHIP_JOINS=="showautoshi")&&($vendor_id == "10")){
          $payment_method_id = mosgetparam( $_REQUEST, 'cc_paymentmethod_id', 0);
          //$_REQUEST["cc_paymentmethod_id"]=94;
          $order_payment_name = mosgetparam( $_REQUEST, 'order_payment_name', 0);
          $order_payment_number1 = mosgetparam( $_REQUEST, 'order_payment_number1', 0);
          $order_payment_number2 = mosgetparam( $_REQUEST, 'order_payment_number2', 0);
          $order_payment_number3 = mosgetparam( $_REQUEST, 'order_payment_number3', 0);
          $order_payment_number4 = mosgetparam( $_REQUEST, 'order_payment_number4', 0);
          $credit_card_code = mosgetparam( $_REQUEST, 'credit_card_code', 0);
          $creditcard_code = mosgetparam( $_REQUEST, 'creditcard_code', 0);
          $order_payment_expire_month = mosgetparam( $_REQUEST, 'order_payment_expire_month', 0);
          $order_payment_expire_year = mosgetparam( $_REQUEST, 'order_payment_expire_year', 0);
          $billing_info_id=mosgetparam( $_REQUEST, 'billing_info_id', '0');

          if($vendor_id == "15" ){
            $cuotas = mosgetparam( $_REQUEST, 'cuotas', 0);
            if(empty($cuotas)|| $cuotas<1 || $cuotas>48){
              $foundError = true;
              $tempErrStep = true;
              $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._CUOTAS_ERROR." <br/>";
              $errorSteps[] = 3;
            }
          }

          $_REQUEST["billing_info_id"]=$_REQUEST['maxUserId'];
          $sql_krcc="SELECT payment_method_id FROM mambophil_pshop_payment_method where payment_method_code='KRCC'";
                          $database->setQuery($sql_krcc);
                          $database->loadObject($sql_krccs);
                 $_REQUEST["payment_method_id"]  =$sql_krccs->payment_method_id;
          $order_payment_number = $order_payment_number1.$order_payment_number2.$order_payment_number3.$order_payment_number4;


    if(((empty($order_payment_number4))||(empty($order_payment_number2))||(!isset($order_payment_number4)))&&($creditcard_code!="amex")){
     //echo "<span class='errorwarning'>"._AUTOSHIPNEW_DESCRIPTION_ERROR."</span>";

       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR1." <br/>";
       $errorSteps[] = 3;

    }else if((empty($order_payment_number1))||(!isset($order_payment_number1))){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR2."<br/>";
       $errorSteps[] = 3;

       }elseif ( ($order_payment_name == '') || ($order_payment_number1 == '' && $order_payment_number2 == '' && $order_payment_number3 == '' && $order_payment_number4 == '')||($credit_card_code == '')){

      $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR3." <br/>";
       $errorSteps[] = 3;
 }

 else {

      $d = $_REQUEST;
      //$result = $ps_checkout->validate_payment_method(&$d, false);
      $result = 1;
      if ( !$result) {
      //echo $order_payment_number;

      $errorMessage = $d["error"];
      //formating the error message display
      $errorMessage1=str_replace("<tr><td spancols=3 class='errorwarning'><tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<BR><span class='errorwarning'>",$errorMessage);
      $errorMessage=str_replace("</td>  <td width='15%'class='errorwarning'>&nbsp;</td></tr>","</span><BR>",$errorMessage1);
      $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$errorMessage);
      $errorMessage1="<input type=\"hidden\" name=\"vpayment\" id=\"vpayment\" value=\"0\" /><table class='errorwarning'> ". $errorMessage1 . "</table>";
      $var_err=1;
       $foundError = true;
       $tempErrStep = true;
       $tempmessage=$errorMessage1;
       $errorSteps[] = 3;

      }

      }

       }
      }else if($payment=="kiccpay")
        {
          //if($vendor_id != "10" ) {  //Korean payment validation disable starts
          $payment_method_id = mosgetparam( $_REQUEST, 'cc_paymentmethod_id', 0);
          $order_payment_name = mosgetparam( $_REQUEST, 'order_payment_name', 0);
          $order_payment_number1 = mosgetparam( $_REQUEST, 'order_payment_number1', 0);
          $order_payment_number2 = mosgetparam( $_REQUEST, 'order_payment_number2', 0);
          $order_payment_number3 = mosgetparam( $_REQUEST, 'order_payment_number3', 0);
          $order_payment_number4 = mosgetparam( $_REQUEST, 'order_payment_number4', 0);
          $credit_card_code = mosgetparam( $_REQUEST, 'credit_card_code', 0);
          $creditcard_code = mosgetparam( $_REQUEST, 'creditcard_code', 0);
          $order_payment_expire_month = mosgetparam( $_REQUEST, 'order_payment_expire_month', 0);
          $order_payment_expire_year = mosgetparam( $_REQUEST, 'order_payment_expire_year', 0);
          $billing_info_id=mosgetparam( $_REQUEST, 'billing_info_id', '0');
		      $financial_months=mosgetparam( $_REQUEST, 'financial_months', '0');

          if($vendor_id == "15" ){
            $cuotas = mosgetparam( $_REQUEST, 'cuotas', 0);
            if(empty($cuotas)|| $cuotas<1 || $cuotas>48){
              $foundError = true;
              $tempErrStep = true;
              $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._CUOTAS_ERROR." <br/>";
              $errorSteps[] = 3;
            }
          }

          $_REQUEST["billing_info_id"]=$_REQUEST['maxUserId'];
          $_REQUEST["payment_method_id"]=$payment_method_id;
          $order_payment_number = $order_payment_number1.$order_payment_number2.$order_payment_number3.$order_payment_number4;


    if(((empty($order_payment_number4))||(empty($order_payment_number2))||(!isset($order_payment_number4)))&&($creditcard_code!="amex")){
     //echo "<span class='errorwarning'>"._AUTOSHIPNEW_DESCRIPTION_ERROR."</span>";

       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR1." <br/>";
       $errorSteps[] = 3;

    }else if((empty($order_payment_number1))||(!isset($order_payment_number1))){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR2."<br/>";
       $errorSteps[] = 3;

       }elseif ( ($order_payment_name == '') || ($order_payment_number1 == '' && $order_payment_number2 == '' && $order_payment_number3 == '' && $order_payment_number4 == '')||($credit_card_code == '' && $vendor_id!=10)){

      $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR3." <br/>";
       $errorSteps[] = 3;
      }
        else {

      $d = $_REQUEST;
      $result = $ps_checkout->validate_payment_method(&$d, false);
      if ( !$result) {
      //echo $order_payment_number;

      $errorMessage = $d["error"];
      //formating the error message display
      $errorMessage1=str_replace("<tr><td spancols=3 class='errorwarning'><tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<BR><span class='errorwarning'>",$errorMessage);
      $errorMessage=str_replace("</td>  <td width='15%'class='errorwarning'>&nbsp;</td></tr>","</span><BR>",$errorMessage1);
      $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$errorMessage);
      $errorMessage1="<input type=\"hidden\" name=\"vpayment\" id=\"vpayment\" value=\"0\" /><table class='errorwarning'> ". $errorMessage1 . "</table>";
      $var_err=1;
       $foundError = true;
       $tempErrStep = true;
       $tempmessage=$errorMessage1;
       $errorSteps[] = 3;
      }
      }
       //} //Korean payment validation disable ends

        }

        else if($payment=="easyswipe")
        {
          //if($vendor_id != "10" ) {  //Korean payment validation disable starts
          $payment_method_id = mosgetparam( $_REQUEST, 'essw_paymentmethod_id', 0);
          $order_payment_name = mosgetparam( $_REQUEST, 'essw_order_payment_name', 0);
          $order_payment_number1 = mosgetparam( $_REQUEST, 'essw_order_payment_number1', 0);
          $order_payment_number2 = mosgetparam( $_REQUEST, 'essw_order_payment_number2', 0);
          $order_payment_number3 = mosgetparam( $_REQUEST, 'essw_order_payment_number3', 0);
          $order_payment_number4 = mosgetparam( $_REQUEST, 'essw_order_payment_number4', 0);
          $credit_card_code = mosgetparam( $_REQUEST, 'essw_credit_card_code', 0);
          $creditcard_code = mosgetparam( $_REQUEST, 'essw_creditcard_code', 0);
          $order_payment_expire_month = mosgetparam( $_REQUEST, 'order_payment_expire_month', 0);
          $order_payment_expire_year = mosgetparam( $_REQUEST, 'order_payment_expire_year', 0);
          $billing_info_id=mosgetparam( $_REQUEST, 'billing_info_id', '0');
		      $financial_months=mosgetparam( $_REQUEST, 'financial_months', '0');
          $order_payment_date= mosGetParam( $_REQUEST, 'essw_order_payment_date', '');
          if($vendor_id == "15" ){
            $cuotas = mosgetparam( $_REQUEST, 'cuotas', 0);
            if(empty($cuotas)|| $cuotas<1 || $cuotas>48){
              $foundError = true;
              $tempErrStep = true;
              $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._CUOTAS_ERROR." <br/>";
              $errorSteps[] = 3;
            }
          }

          $_REQUEST["billing_info_id"]=$_REQUEST['maxUserId'];
          $_REQUEST["payment_method_id"]=$payment_method_id;
          $order_payment_number = $order_payment_number1.$order_payment_number2.$order_payment_number3.$order_payment_number4;
    if(((empty($order_payment_number4))||(empty($order_payment_number2))||(!isset($order_payment_number4)))){
     //echo "<span class='errorwarning'>"._AUTOSHIPNEW_DESCRIPTION_ERROR."</span>";

       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR1." <br/>";
       $errorSteps[] = 3;

    }else if((empty($order_payment_number1))||(!isset($order_payment_number1))){
       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR2."<br/>";
       $errorSteps[] = 3;
          echo "test".$order_payment_name;
       }elseif ( ($order_payment_name == '') || ($order_payment_number1 == '' && $order_payment_number2 == '' && $order_payment_number3 == '' && $order_payment_number4 == '')){
      $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR3." <br/>";
       $errorSteps[] = 3;
      }
        else {

      $d = $_REQUEST;
      $result = $ps_checkout->validate_payment_method(&$d, false);
      if ( !$result) {
      //echo $order_payment_number;

      $errorMessage = $d["error"];
      //formating the error message display
      $errorMessage1=str_replace("<tr><td spancols=3 class='errorwarning'><tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<BR><span class='errorwarning'>",$errorMessage);
      $errorMessage=str_replace("</td>  <td width='15%'class='errorwarning'>&nbsp;</td></tr>","</span><BR>",$errorMessage1);
      $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$errorMessage);
      $errorMessage1="<input type=\"hidden\" name=\"vpayment\" id=\"vpayment\" value=\"0\" /><table class='errorwarning'> ". $errorMessage1 . "</table>";
      $var_err=1;
       $foundError = true;
       $tempErrStep = true;
       $tempmessage=$errorMessage1;
       $errorSteps[] = 3;
      }
      }
       //} //Korean payment validation disable ends

        }

    else if($payment=="fcpay") {

        require_once(CLASSPATH . 'ps_payment_method.php');
        $ps_payment_method = new ps_payment_method;
        $payment_method_id = mosgetparam($_REQUEST, 'fc_paymentmethod_id', 0);
        $order_payment_name = mosgetparam($_REQUEST, 'fc_order_payment_name', 0);
        $order_payment_number1 = mosgetparam($_REQUEST, 'fc_order_payment_number1', 0);
        $order_payment_number2 = mosgetparam($_REQUEST, 'fc_order_payment_number2', 0);
        $order_payment_number3 = mosgetparam($_REQUEST, 'fc_order_payment_number3', 0);
        $order_payment_number4 = mosgetparam($_REQUEST, 'fc_order_payment_number4', 0);
        $credit_card_code = mosgetparam($_REQUEST, 'fc_credit_card_code', 0);
        $creditcard_code = mosgetparam($_REQUEST, 'financialcard_code', 0);
        $order_payment_expire_month = mosgetparam($_REQUEST, 'fc_order_payment_expire_month', 0);
        $order_payment_expire_year = mosgetparam($_REQUEST, 'fc_order_payment_expire_year', 0);
        $billing_info_id = mosgetparam($_REQUEST, 'billing_info_id', '0');
        $financial_months = mosgetparam($_REQUEST, 'financial_months', '0');

        $_REQUEST["billing_info_id"] = $_REQUEST['maxUserId'];
        $_REQUEST["payment_method_id"] = $payment_method_id;
        $order_payment_number = $order_payment_number1 . $order_payment_number2 . $order_payment_number3 . $order_payment_number4;

        $order_payment_number_count = mb_strlen($order_payment_number);
        $creditcard_code_array = array("VISA", "MC");

        //Validation number card digit only mexico VISA and mastercard
        if ($vendor_id == "1" && $order_payment_number_count != 16 && in_array($creditcard_code, $creditcard_code_array)) {
            $foundError = true;
            $tempErrStep = true;
            $tempmessage = "<br>&nbsp;&nbsp;&nbsp;-" . _NUMBER_CARD_DIGIT . "<br/>";
            $errorSteps[] = 3;
        }

        if(((empty($order_payment_number4))||(empty($order_payment_number2))||(!isset($order_payment_number4)))&&($creditcard_code!="amex")){
        //echo "<span class='errorwarning'>"._AUTOSHIPNEW_DESCRIPTION_ERROR."</span>";

          $foundError = true;
          $tempErrStep = true;
          $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR1." <br/>";
          $errorSteps[] = 3;

        }else if((empty($order_payment_number1))||(!isset($order_payment_number1))){

          $foundError = true;
          $tempErrStep = true;
          $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR2."<br/>";
          $errorSteps[] = 3;

          }elseif ( ($order_payment_name == '') || ($order_payment_number1 == '' && $order_payment_number2 == '' && $order_payment_number3 == '' && $order_payment_number4 == '')||($credit_card_code == '')){

          $foundError = true;
          $tempErrStep = true;
          $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR3." <br/>";
          $errorSteps[] = 3;
        }elseif($ps_payment_method->payUBankNameValidate($order_payment_name) && $vendor_id==1){
          $foundError = true;
          $tempErrStep = true;
          $tempmessage="<br>&nbsp;&nbsp;&nbsp;- ".$PHPSHOP_LANG->_PHPSHOP_PAYU_CARD_NAME_ERROR." <br/>";
          $errorSteps[] = 3;
        } else {
            $d = $_REQUEST;
            $result = $ps_checkout->validate_payment_method(&$d, false);
            if (!$result) {
                //echo $order_payment_number;

                $errorMessage = $d["error"];
                //formating the error message display
                $errorMessage1 = str_replace("<tr><td spancols=3 class='errorwarning'><tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>",
                    "<BR><span class='errorwarning'>", $errorMessage);
                $errorMessage = str_replace("</td>  <td width='15%'class='errorwarning'>&nbsp;</td></tr>",
                    "</span><BR>", $errorMessage1);
                $errorMessage1 = str_replace("<tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>",
                    "<span class='errorwarning'>", $errorMessage);
                $errorMessage1 = "<input type=\"hidden\" name=\"vpayment\" id=\"vpayment\" value=\"0\" /><table class='errorwarning'> " . $errorMessage1 . "</table>";
                $var_err = 1;
                $foundError = true;
                $tempErrStep = true;
                $tempmessage = $errorMessage1;
                $errorSteps[] = 3;
            }
        }

      }

          else if($payment=="dbpay")
        {

        $join=$_REQUEST["joinproductradio"];
  $AUTOSHIP_JOINS=$_REQUEST["showautoship"];
  $joinplans=explode("-",$join);
  //echo $joinplans[1];
     if($AUTOSHIP_JOINS=="showautoshi" && $vendor_id!=12) {
     //echo "thisset";
      $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_AUTOSHIP_WITHOUT_CREDIT." <br/>";
       $errorSteps[] = 3;
     }

          $payment_method_id = mosgetparam( $_REQUEST, 'db_paymentmethod_id', 0);
          $order_payment_name = mosgetparam( $_REQUEST, 'db_order_payment_name', 0);
          $order_payment_number1 = mosgetparam( $_REQUEST, 'db_order_payment_number1', 0);
          $order_payment_number2 = mosgetparam( $_REQUEST, 'db_order_payment_number2', 0);
          $order_payment_number3 = mosgetparam( $_REQUEST, 'db_order_payment_number3', 0);
          $order_payment_number4 = mosgetparam( $_REQUEST, 'db_order_payment_number4', 0);
          $credit_card_code = mosgetparam( $_REQUEST, 'db_credit_card_code', 0);
          $creditcard_code = mosgetparam( $_REQUEST, 'db_creditcard_code', 0);
          $order_payment_expire_month = mosgetparam( $_REQUEST, 'db_order_payment_expire_month', 0);
          $order_payment_expire_year = mosgetparam( $_REQUEST, 'db_order_payment_expire_year', 0);
          $billing_info_id=mosgetparam( $_REQUEST, 'billing_info_id', '0');

          $_REQUEST["billing_info_id"]=$_REQUEST['maxUserId'];
          $_REQUEST["payment_method_id"]=$payment_method_id;
          $order_payment_number = $order_payment_number1.$order_payment_number2.$order_payment_number3.$order_payment_number4;


    if(((empty($order_payment_number4))||(empty($order_payment_number2))||(!isset($order_payment_number4)))&&($creditcard_code!="amex")){
     //echo "<span class='errorwarning'>"._AUTOSHIPNEW_DESCRIPTION_ERROR."</span>";

       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR1." <br/>";
       $errorSteps[] = 3;

    }else if((empty($order_payment_number1))||(!isset($order_payment_number1))){

       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR2."<br/>";
       $errorSteps[] = 3;

       }elseif ( ($order_payment_name == '') || ($order_payment_number1 == '' && $order_payment_number2 == '' && $order_payment_number3 == '' && $order_payment_number4 == '')||($credit_card_code == '')){

      $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_CREDIT_CARD_ERROR3." <br/>";
       $errorSteps[] = 3;
 }

 else {


      $d = $_REQUEST;
      $result = $ps_checkout->validate_payment_method(&$d, false);
      if ( !$result) {
      //echo $order_payment_number;



      $errorMessage = $d["error"];
      //formating the error message display
      $errorMessage1=str_replace("<tr><td spancols=3 class='errorwarning'><tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<BR><span class='errorwarning'>",$errorMessage);
      $errorMessage=str_replace("</td>  <td width='15%'class='errorwarning'>&nbsp;</td></tr>","</span><BR>",$errorMessage1);
      $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'>Credit Card</td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$errorMessage);
      $errorMessage1="<input type=\"hidden\" name=\"vpayment\" id=\"vpayment\" value=\"0\" /><table class='errorwarning'> ". $errorMessage1 . "</table>";
      $var_err=1;
       $foundError = true;
       $tempErrStep = true;
       $tempmessage=$errorMessage1;
       $errorSteps[] = 3;

          }
        }
      }



      else if($payment=="payck"){ 
  $payment_method_id = mosgetparam( $_REQUEST, 'check_paymentmethod_id', 0);
  $paymentmethod_check = mosgetparam( $_REQUEST, 'paymentmethod_check', 0);
  $_REQUEST['payment_method_id']=$_REQUEST['check_paymentmethod_id'];
	$payment_chk_routing_number = mosgetparam( $_REQUEST, 'payment_chk_routing_number', 0);
	$payment_chk_account_number = mosgetparam( $_REQUEST, 'payment_chk_account_number', 0);
	$payment_chk_account_name = mosgetparam( $_REQUEST, 'payment_chk_account_name', 0);
	$payment_chk_account_type = mosgetparam( $_REQUEST, 'payment_chk_account_type', 0);
	$payment_chk_type = mosgetparam( $_REQUEST, 'payment_chk_type', 0);

  $join=$_REQUEST["joinproductradio"];
  $AUTOSHIP_JOINS=$_REQUEST["showautoship"];
  $joinplans=explode("-",$join);
  //echo $joinplans[1];
    if($AUTOSHIP_JOINS=="showautoshi" && $vendor_id!=12) {
      if($vendor_id != "9" ) {
     //echo "thisset";
        $foundError = true;
        $tempErrStep = true;
        $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_AUTOSHIP_WITHOUT_CREDIT." <br/>";
        $errorSteps[] = 3;
      }
    }
      $d = $_REQUEST;
      $result = $ps_checkout->validate_payment_method(&$d, false);
      if (! $result) {
      //echo $order_payment_number;


      $errorMessage = $d["error"];
      //formating the error message display
      $errorMessage1=str_replace("<tr><td spancols=3 class='errorwarning'><tr>  <td width='30%' class='errorwarning'>Check</td>  <td width='50%' class='errorwarning'>","<BR><span class='errorwarning'>",$errorMessage);
      $errorMessage=str_replace("</td>  <td width='15%'>&nbsp;</td></tr>","</span><BR>",$errorMessage1);
      $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'>Check</td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$errorMessage);
      $errorMessage1="<input type=\"hidden\" name=\"vpayment\" id=\"vpayment\" value=\"0\" /><table>". $errorMessage1 . "</table>";
     // $errorMessage1="<span class='errorwarning'>". $errorMessage . "</span>";
      $var_err=1;
      $foundError = true;
       $tempErrStep = true;
       $tempmessage=$errorMessage1;
       $errorSteps[] = 3;

      }

      }else{
        $AUTOSHIP_JOINS=$_REQUEST["showautoship"];
        if($_REQUEST["otherpaymentmethods"]==""){
        $foundError = true;
               $tempErrStep = true;
               $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_OTHER_PAYMENT_SELECTION."<br/>";
               $errorSteps[] = 3;
        }

        //get payment method code
        require_once(CLASSPATH . 'ps_payment_method.php');
        $ps_payment_method = new ps_payment_method;
        $payment_method_code = $ps_payment_method->get_field($_REQUEST["otherpaymentmethods"], "payment_method_code");

        //$order_amount=HTML_iboRegistration::taxandtotalofallproducts($_REQUEST);
        if(empty ($_REQUEST["amount"]) && $counts <=0){
          $_REQUEST["amount"] = $order_amount;
        }
        //for payu oxxo, do validation for amount limit 10000MXP
        if($payment_method_code == 'PAYU' && $_REQUEST["payu_option"]=="OXXO" && $_REQUEST["amount"]>10000){ 
          $result = $ps_checkout->validate_payment_method(&$d, false);
          $d["error"] = $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_PAYU_LIMIT;
          $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_PAYU_LIMIT;
          $foundError = true;
          $tempErrStep = true;
          $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
          $tempmessage=$errorMessage1;
          $errorSteps[] = 3;
        }

        if(($_REQUEST["otherpaymentmethods"]==40)||($_REQUEST["otherpaymentmethods"]==31)){
         $bankdate=$_REQUEST["payment_bank_receipt_date"];
         $splitdate = explode("/",$bankdate);
         if (empty($_REQUEST["payment_bank_receipt"]) || (strlen($_REQUEST["payment_bank_receipt"]) != 10 && strlen($_REQUEST["payment_bank_receipt"]) != 6)|| is_numeric($_REQUEST["payment_bank_receipt"]) == 0){
          $d["error"] = $PHPSHOP_LANG->_PHPSHOP_RECEIPT_NUMBER_DIGIT_LENGTH_CHECK;
          $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_RECEIPT_NUMBER_DIGIT_LENGTH_CHECK;
          $foundError = true;
          $tempErrStep = true;
          $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
          $tempmessage=$errorMessage1;
          $errorSteps[] = 3;
         }
          if (empty($_REQUEST["payment_bank_receipt_date"]) || checkdate($splitdate[1], $splitdate[0], $splitdate[2])== 0){
               $d["error"] = $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_BANK_INVALID_RECEIPT_DATE;
               $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_BANK_INVALID_RECEIPT_DATE;
               $foundError = true;
              $tempErrStep = true;
              $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
              $tempmessage=$errorMessage1;
              $errorSteps[] = 3;
           }

        }
        if($_REQUEST["otherpaymentmethods"]==114){
          $bankdate=$_REQUEST["wire_confirmation_date"];
          $splitdate = explode("/",$bankdate);
          if (empty($_REQUEST["wire_confirmation_number"]) || (strlen($_REQUEST["wire_confirmation_number"]) != 10 && strlen($_REQUEST["wire_confirmation_number"]) != 6)|| is_numeric($_REQUEST["wire_confirmation_number"]) == 0){
           $d["error"] = $PHPSHOP_LANG->_PHPSHOP_RECEIPT_NUMBER_DIGIT_LENGTH_CHECK;
           $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_RECEIPT_NUMBER_DIGIT_LENGTH_CHECK;
           $foundError = true;
           $tempErrStep = true;
           $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
           $tempmessage=$errorMessage1;
           $errorSteps[] = 3;
          }
           if (empty($_REQUEST["wire_confirmation_date"]) || checkdate($splitdate[1], $splitdate[0], $splitdate[2])== 0){
                $d["error"] = $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_BANK_INVALID_RECEIPT_DATE;
                $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_BANK_INVALID_RECEIPT_DATE;
                $foundError = true;
               $tempErrStep = true;
               $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
               $tempmessage=$errorMessage1;
               $errorSteps[] = 3;
            }
 
         }
        if(($_REQUEST["otherpaymentmethods"]==38)){
          $splitdate = explode("/", $_REQUEST["payment_bank_receipt_date"]);
          if (empty($_REQUEST['payment_bank_acct_name'])) {
            $d["error"] = $PHPSHOP_LANG->_PHPSHOP_CHK_ACCOUNT_NAME_NULL;
            $d["errorstring"] .= $PHPSHOP_LANG->_PHPSHOP_CHK_ACCOUNT_NAME_NULL;
            $foundError = true;
            $tempErrStep = true;

            $errorMessage1 .="<br>&nbsp;&nbsp;&nbsp;- ".$PHPSHOP_LANG->_PHPSHOP_CHK_ACCOUNT_NAME_NULL." <br/>";
            $tempmessage=$errorMessage1;
            $errorSteps[] = 3;
          }
          if (empty($_REQUEST['cc_bank_cd'])) {
            $d["error"] = $PHPSHOP_LANG->_PHPSHOP_ELECTRONIC_BANK_NULL;
            $d["errorstring"] .= $PHPSHOP_LANG->_PHPSHOP_ELECTRONIC_BANK_NULL;
            $foundError = true;
            $tempErrStep = true;
            $errorMessage1 .="<br>&nbsp;&nbsp;&nbsp;- ".$PHPSHOP_LANG->_PHPSHOP_ELECTRONIC_BANK_NULL." <br/>";
            $tempmessage=$errorMessage1;
            $errorSteps[] = 3;
          }
          if ((checkdate($splitdate[1],$splitdate[0],$splitdate[2])===false) ||
           (!preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/",$_REQUEST["payment_bank_receipt_date"]))){
            $d["error"] = $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_BANK_INVALID_RECEIPT_DATE;
            $d["errorstring"] .= $PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_BANK_INVALID_RECEIPT_DATE;
            $foundError = true;
            $tempErrStep = true;
            $errorMessage1 .="<br>&nbsp;&nbsp;&nbsp;- ".$PHPSHOP_LANG->_PHPSHOP_CHECKOUT_ERR_BANK_INVALID_RECEIPT_DATE." <br/>";
            $tempmessage=$errorMessage1;
            $errorSteps[] = 3;
          }

        }
        $selected_otherpayment_code = "";
        if($_REQUEST["otherpaymentmethods"] > 0){
        $pay_methods = getPaymentMethodDetails($_REQUEST["otherpaymentmethods"]);
        $selected_otherpayment_code =  $pay_methods->payment_method_code;
        }       
        if($AUTOSHIP_JOINS=="showautoshi" && $vendor_id!=12 && $selected_otherpayment_code != "PSTD") {
          if($vendor_id==9)
              $emsg = _NEWREGISTRATION_ERROR_AUTOSHIP_WITHOUT_CREDIT_CHK;
          else
              $emsg = _NEWREGISTRATION_ERROR_AUTOSHIP_WITHOUT_CREDIT;
              $foundError = true;
               $tempErrStep = true;
               $tempmessage="<br>&nbsp;&nbsp;&nbsp;- ".$emsg." <br/>";
               $errorSteps[] = 3;
     }
      }
      /********************Valida 80BV solo para México si es menor a esto manda error.*****************************/
      if($vendor_id==1 && $_REQUEST["showautoship"] =="showautoshi"){

      	$product_sales_BV = 0.0;
      	foreach($_REQUEST["autoproduct_list"] as $product){
      		list($prod_id, $qty) = explode('|', $product);
      		$sql = "SELECT p.product_id, p.volume, IFNULL(a.`product_id`,0)as autopromo
                  FROM mambophil_pshop_product p LEFT JOIN mambophil_pshop_product_attribute a
                  ON p.`product_id` = a.`product_id` AND a.attribute_name = 'auto_promo'
                  WHERE p.product_id = " . $prod_id;
      		//echo $sql."<br>";
      		$prod =NULL;
      		$database->setQuery($sql);
      		$database->loadObject($prod);

          //bv is not considered for auto_promo products. so only add bv of other products
          if($prod->autopromo==0){
      		  $product_sales_BV += $prod->volume*$qty;
      		}
      	}

      	if($product_sales_BV < 80){
      		$foundError = true;
      		$tempErrStep = true;
      		$tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._AUTOSHIP_NEW_BV_MESSAGE."&nbsp;". $product_sales_BV.
          " BV <br/> &nbsp;&nbsp;&nbsp;"._AUTOSHIP_BV_NEEDED_ADDITIONAL_INFO."<br/>";
      		$errorSteps[] = 2;
      	}

      }
      //echo "$product_sales_BV-----";
      /********************Fin Valida 80BV solo para México si es menor a esto manda error.*****************************/
       //Start of RFC error details

       if($_REQUEST["rfc_select"]==1){

       $rfcMessage1 = validateRfc($_REQUEST["RFC"],'1','ac1');
          $rfcMessage2 = validateRfc($_REQUEST["RFC"],'1','ac2');
    if ($rfcMessage1==false && $rfcMessage2==false) {


               $d["errorstring"].= "<br>(RFC) ";
               $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_INVALID_FORMAT;
               $foundError = true;
              $tempErrStep = true;
              $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
              $tempmessage=$errorMessage1;
              $errorSteps[] = 3;
  } 
$S_num=trim($_REQUEST["StreetNumber"]);
$RFC=trim($_REQUEST["RFC"]);
$S_name=trim($_REQUEST["StreetName"]);
$addr_2=trim($_REQUEST["rfc_address_2"]);
$ZIP=trim($_REQUEST["rfc_zip"]);
$State=trim($_REQUEST["rfc_state"]);
$RS=trim($_REQUEST["RS"]) ;
$CFDI =trim($_REQUEST["cfdi"]) ;
 $city=trim($_REQUEST["rfc_city"]);
 $Curp=trim($_REQUEST["CURP"]);
 $CFDI_regimen=trim($_REQUEST["cfdi_regimen"]);
if(empty($CFDI)||//true
    (empty($S_num))||//true
    (empty($RFC))||//true
    (empty($S_name))||//true
    (empty($addr_2))||//true
    (empty($ZIP))||//true
    (empty($State))||//true
    (empty($RS))||//true
    (empty($city)) ||
    (empty($CFDI_regimen))
    )//true
    {
     
               $d["errorstring"].= "<br>(RFC)  ";
               $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_RFC_TEXT_ERROR_MESSAGE;
               $foundError = true;
              $tempErrStep = true;
              $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
              $tempmessage=$errorMessage1;
              $errorSteps[] = 3;


}/*else{
/*	if( ($_REQUEST["Type_RFC"]=="F") && (empty($Curp))){
  	          $d["errorstring"].= "<br>(RFC) ";
               $d["errorstring"].= $PHPSHOP_LANG->_PHPSHOP_RFC_TEXT_ERROR_MESSAGE;
               $foundError = true;
              $tempErrStep = true;
              $errorMessage1=str_replace("<tr>  <td width='30%' class='errorwarning'></td>  <td width='50%' class='errorwarning'>","<span class='errorwarning'>",$d["errorstring"]);
              $tempmessage=$errorMessage1;
              $errorSteps[] = 3;


	  }*/

//}*/
  }
       //


      if($tempErrStep)
             {
             $message.= "<h3>"._NEWREGISTRATION_ERROR_PAYMENT_STEPS." </h3>".$tempmessage;
             }




  //End Of Validate Step 9: Payment Information
   }
  }

  //Validation for steep 3 autoship's terms and condition
  $terms_cond_autoship = $_REQUEST["chkTerm_autoship"];
  $showautoship = $_REQUEST["showautoship"];
  if ($showautoship == "showautoshi") {
    if ($terms_cond_autoship != "1") {
      $message.= "<br>&nbsp;&nbsp;&nbsp;- Step 3: "._NEWREGISTRATION_ERROR_TERMSCONDITIONS_AUTOSHIP."</br>";
      $foundError = true;
    }
  }

  //Validate Terms And Conditions
  $tempmessage="";
  $tempErrStep = false;
  $policy=$_REQUEST[termsandconditions];
  if($policy=="")
       {
       $foundError = true;
       $tempErrStep = true;
       $tempmessage="<br>&nbsp;&nbsp;&nbsp;- "._NEWREGISTRATION_ERROR_TERMSCONDITIONS." <br/>";
       $errorSteps[] = 3;
        }
      if($tempErrStep)
             {
             $message.= "<h3>"._NEWREGISTRATION_ERROR_HEADINGS_TERMS."</h3>".$tempmessage;
             }
          $tempmessage="";
   //End Of Validate Terms And Conditions
     if($foundError)
     {
     $message= "<h3>"._NEWREGISTRATION_ERROR_HEADINGS." </h3>".$message;
     //following hidden variable is used for finding the total order amount for multiple payment
      ?>
       <input type="hidden" id="returnordertotal" name="returnordertotal" value="<? echo $order_amount;?>">
       <?
          }



            $rtn["foundError"] = $foundError;
            $rtn["errorMessage"] = $message;
            $rtn["errorSteps"] = array_unique($errorSteps); //implode(",",);
            return $rtn;
 }


//validation for Korean cancellation  ENZ-3294 JIRA IMPLEMENTATION

  function validateKoreanCancellationRules($koreanName,$fullBirth){
           global $database;

            $sql = "SELECT u.username AS ibo,u.id as userid FROM mambophil_users u
            INNER JOIN mambophil_adv_users au ON au.id = u.id
            WHERE u.`kssn_name` = '$koreanName' AND u.usertype = 'Enzacta IBO' AND u.bday = '$fullBirth'";
            
              $database->setQuery($sql);
              
              $Display=$database->loadObjectList();
               
             foreach($Display as $values){
                 $sql_Cancel_log ="SELECT id, TIMESTAMPDIFF(MONTH, canceled_date,CURDATE()) AS difference FROM mambophil_adv_users WHERE 
                                   id='$values->userid'";
                  $database->setQuery($sql_Cancel_log);
                  $rtn_log = null;
                  $database ->loadObject($rtn_log);  
                             
              if($rtn_log->difference<6){
                        //return false;
                       //find that IBO cancel
                        $sql_help_log="SELECT id, TIMESTAMPDIFF(MONTH, indate,CURDATE()) AS difference FROM mambophil_helpdesk_tickets WHERE 
                                 userid='$rtn_log->id' AND parentid =17 AND catid=21 ORDER BY id DESC LIMIT 0,1";
                                 
                              $database->setQuery($sql_help_log);
                              $help_log = null;
                              $database->loadObject($help_log);   

                              if(empty($help_log->id)){
                                 return false;
                              }
                      }
             }
             return true;
  }




/**************************************************************************************************************
* Name   : SaveRegistration
* Author :
* Date   :
* Desc   : Saves The IBO Registation
*
*
****************************************************************************************************************/



function SaveRegistration(){

          global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY; 
  
    $Last_Username=$_SESSION["lastusername"];
	$Originaluser_id = mosgetparam( $_REQUEST, 'Originaluser_id', 0);
	if($_SESSION['auth']['user_id'] != $Originaluser_id)
	{
		die('<script>alert("'._SESSION_EXPIRED_REGISTER.'");location.href="'.$mosConfig_live_site.'"</script>');
	}
    $SQL_COUNT="SELECT COUNT(username) as counts FROM mambophil_users where username=$Last_Username";
                 $database->setQuery($SQL_COUNT);
                 $database->loadObject($IBO_COUNTS);
                 $IBO_COUNT=$IBO_COUNTS->counts;
             $paypal_confirmation = 0;
             if($IBO_COUNT && isset($_REQUEST['order']) && $_SESSION['paypal_reg_mail'] == 1 ) {
                $paypal_confirmation = 1;
                unset($_SESSION['custom']); 
                unset($_SESSION['custom_subscr']); 
                unset($_SESSION['reg_autoship_enabled']); 
             }
             $taishin_confirmation = 0;
             if($IBO_COUNT && isset($_REQUEST['order']) && $_SESSION['taishin_reg_mail'] == 1 ) {
                $taishin_confirmation = 1;
             }

if(empty($IBO_COUNT)){

        SaveIBOEnrollDetails();

    //Checking that the User enroll with autoship item for saving the autoship items
        $JOIN=$_REQUEST["joinproductradio"];
        $Auto_flag=explode("-",$JOIN);
        $sql_status_auto="SELECT order_vol_status_code FROM mambophil_pshop_order_status where order_status_code='".$_SESSION['reg_order_stat']."' ";

        $database->setQuery($sql_status_auto);
        $database->loadObject($newOrderstatusauto);
    //if($newOrderstatusauto->order_vol_status_code=="C"){

     $sql_status_auto="SELECT order_vol_status_code FROM mambophil_pshop_order_status where order_status_code='".$_SESSION['reg_order_stat']."' ";

        $database->setQuery($sql_status_auto);
        $database->loadObject($newOrderstatusauto);
    //if($newOrderstatusauto->order_vol_status_code=="C"){

    if($_REQUEST["country"]=="KOR"){
       $Auto_flag[1]="";
       $_REQUEST["showautoship"]="";
    }

    //}
       $Placed_orderstatus= $_SESSION['reg_order_stat'];
       $ps_vendor_id=$_REQUEST['ps_vendor_id'];
       $pibo=$_REQUEST["pibo"];

     ShowConfiramtionmessages($ps_vendor_id,$Placed_orderstatus,$pibo);
       unset($values);
       unset($_SESSION["store"]);
       unset($_SESSION['KOREAN_REG_VALUES']) ;
  }else if($paypal_confirmation){
       $Placed_orderstatus= $_SESSION['reg_order_stat'];
       $ps_vendor_id=$_REQUEST['ps_vendor_id'];
       $pibo="";
       $_REQUEST["name"] = $_SESSION['autoship_username'];
       $_REQUEST["password"] = $_SESSION["autoship_password"];
       $sql = "SELECT u.id,au.cntry_join_cd FROM mambophil_users u INNER JOIN mambophil_adv_users au ON u.id = au.id  WHERE u.username='".$_SESSION['autoship_username']."'";
       $database->setQuery( $sql );
       $database->loadObject( $my_id );
       IBOActivation($Placed_orderstatus,$my_id->id,$my_id->cntry_join_cd);
       emailSendingPaypalConfirmation();  
       ShowConfiramtionmessages($ps_vendor_id,$Placed_orderstatus,$pibo); 
       unset($values);  
       unset($_SESSION["store"]);  
    }else if($taishin_confirmation){     
       $Placed_orderstatus= $_SESSION['reg_order_stat'];
       $ps_vendor_id=$_REQUEST['ps_vendor_id'];
       $pibo="";
       $_REQUEST["name"] = $_SESSION['autoship_username']; 
       $_REQUEST["password"] = $_SESSION["autoship_password"];
       $sql = "SELECT u.id,au.cntry_join_cd FROM mambophil_users u INNER JOIN mambophil_adv_users au ON u.id = au.id  WHERE u.username='".$_SESSION['autoship_username']."'";
       $database->setQuery( $sql );
       $database->loadObject( $my_id );
       IBOActivation($Placed_orderstatus,$my_id->id,$my_id->cntry_join_cd);
       emailSendingPaypalConfirmation();  
       ShowConfiramtionmessages($ps_vendor_id,$Placed_orderstatus,$pibo); 
       unset($values);  
       unset($_SESSION["store"]);   
  }else{    
       showIBOplacedmessage($Last_Username);
  }
       echo "</br>";
       PrintEditionOfIBOApplication($Last_Username);
}

/**************************************************************************************************************
* Name   : SaveIBOEnrollDetails
* Author :
* Date   :
* Desc   : Saves The IBO Enrollment Details
*
*
****************************************************************************************************************/

function SaveIBOEnrollDetails(){
  global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY;
  include( $GLOBALS['mosConfig_absolute_path'] . '/components/com_iboregistration/Registration_Serialization.php' );
  require_once( $mosConfig_absolute_path.'/components/com_advanced_registration/advanced_registration_functions.php' );
  $currentUsers = $mainframe->getUser();
         $store = $_SESSION["store"];
         $values = unserialize($store);
         $Textboxvalues=$values->requestvalues;

         $enrol_type = $values->enrolType;
         $enrollcountry=$values->enrollCountry;
         $enrollcountrycode=$values->enrollcountrycode;

         $maxUserId = $values->maxUserId;
 //following condition is used to remove the ind and bus from the REQUEST
foreach($Textboxvalues as $key=>$value){
  if($enrol_type == 'Individual'){
    if(strpos($key, 'ind') !== FALSE && strpos($key, 'ind') == 0 ){
      $key_new =  substr($key, 3);
      $Textboxvalues[$key_new] = $Textboxvalues[$key];
    }
  }else{
    if(strpos($key, 'bus') !== FALSE && strpos($key, 'bus') == 0 ){
      //echo "<br>KEY: $key VALUE: $value";
      $key_new =  substr($key, 3);
      $Textboxvalues[$key_new] = $Textboxvalues[$key];
    }
  }

}
//settting the serialzied values into request
$_REQUEST = $Textboxvalues;	  
 if($enrol_type=='Individual'){
 $_REQUEST["name"]=$values->indi_nickname;
 }else{
 $_REQUEST["name"]=$values->busi_nickname;
 }


       $_REQUEST["username"]=$maxUserId;
       $_REQUEST["contract_signed_ind"] = $_REQUEST["termsandconditions"];
       
       $country=$enrollcountrycode;
       $username=$maxUserId;
       $_REQUEST["country"]=$enrollcountrycode;
       $_REQUEST["address_1"]=$values->billingaddress1;

       $_REQUEST["address_type_name"]="Billing";
       if($enrol_type=='Individual')
       {
         if($_REQUEST["contryid"]==64){
           $_REQUEST["gst_registered"]=$_REQUEST["indgst_registered"];
         }
         
         if($_REQUEST["contryid"]==73 && $_REQUEST["contryid"]==886)
         {

          $_REQUEST["tax_id"]=$values->indi_ssn1."".$values->indi_ssn2."".$values->indi_ssn3;

          }
         else
         {
         $_REQUEST["tax_id"]=$_REQUEST["indtax_id"];
          }
        }
      else{
      if(!empty( $_REQUEST["business_taxid1"])){
        if($_REQUEST["contryid"]==64){
           $_REQUEST["gst_registered"]=$_REQUEST["busgst_registered"];
         }
         
         $_REQUEST["tax_id"]=$_REQUEST["business_taxid1"]."".$_REQUEST["business_taxid2"];
         }else{
          $_REQUEST["tax_id"]=$_REQUEST["bustax_id"];
         }
       }


       
       $_REQUEST["gst_registered"] = ($_REQUEST["gst_registered"] == "Yes") ? 1 : 0;
       
       $_REQUEST["cntry_join_cd"]=$values->enrollcontryid; 
       $_REQUEST["source"] = $_SERVER['HTTP_HOST'];       
       if($enrollcountry==1){
         $_REQUEST["city"]=$values->mexbillcity;
         $_REQUEST["state"]=$values->mexbillstates;
         $_REQUEST["zip"]=$values->mexbillzip;
         $_REQUEST["address_2"]=$values->mexbillarea;
       }else{
         $_REQUEST["address_2"]= $values->billingaddress2;
         $_REQUEST["city"]=$values->billingcity;
         $_REQUEST["state"]=$values->billingstate;
       if($enrollcountry==17){
       $_REQUEST["zip"]=$values->bill_zip1." ".$values->bill_zip2;
       }
       else
       {
        $_REQUEST["zip"]=$values->billingzip;
       }
       if($_REQUEST["contryid"]==73){
        if($enrol_type=='Individual'){
          $_REQUEST["cell_phone"]=$values->indi_cellphone1.$values->indi_cellphone2.$values->indi_cellphone3;
        }
        else
        {
          $_REQUEST["cell_phone"]=$values->busi_cellphone1.$values->busi_cellphone2.$values->busi_cellphone3;
        }
       }
       else
       {
        //
       }
       }

     if($enrollcountry == 10){
        if($enrol_type=='Individual'){
          $byear = $values->indbyear;
          $bmonth = $values->indbmonth;
          $bdate = $values->indbdate;
        } else {
          $byear = $values->busbyear;
          $bmonth = $values->busbmonth;
          $bdate = $values->busbdate;
        }
        $_REQUEST["bday"] = $byear."-".$bmonth."-".$bdate;

        // For Korea make first part(6 digit) of SSN from date of birth
        $by = substr($byear, -2);
        $bm = sprintf("%02d", $bmonth);
        $bd = sprintf("%02d", $bdate);

        $_REQUEST["jumin1"] = $by.$bm.$bd.'-0000000';
        $_REQUEST["jumin2"] = '';
     }else{
        $_REQUEST["bday"] = $values->byear."-".$values->ibobirthmonth."-".$values->bdate;
     }

     if($enrollcountry != 10){
        if($enrol_type=='Individual'){
          $byear = $values->indbyear;
          $bmonth = $values->indbmonth;
          $bdate = $values->indbdate;
        } else {
          $byear = $values->busbyear;
          $bmonth = $values->busbmonth;
          $bdate = $values->busbdate;
        }
         $_REQUEST["bday"] = $byear."-".$bmonth."-".$bdate;
     }

     $_REQUEST["coapp_bday"]=$values->cobyear."-".$values->coibobirthmonth."-".$values->cobdate;

      $_REQUEST["district"]=$values->district;
      $_REQUEST["section"]=$values->section;
      $_REQUEST["lane"]=$values->lane;
      $_REQUEST["alley"]=$values->alley;
      $_REQUEST["cntry_lang_cd"]= '';
      if( isset($_REQUEST["starterkit"]) && !empty($_REQUEST["starterkit"]) ){ 
      $language_value="SELECT business_kits_language FROM mambophil_plantypes where plan_product_id='".$_REQUEST["starterkit"]."'";
                            $database->setQuery($language_value);
                            $database->loadObject($languageset_value);
                            $_REQUEST["cntry_lang_cd"] = $languageset_value->business_kits_language;
      }  
      $database1="SELECT l.language_id as lid ,c.default_time_zone_id as time_zone_id
                  from mambophil_pshop_language l INNER JOIN  mambophil_pshop_country c on l.country_id=c.country_id
                  INNER JOIN mambophil_pshop_vendor  v on c.country_3_code=v.vendor_country
                  WHERE v.vendor_id =$enrollcountry";
 
                  $database->setQuery($database1);
                  $database ->loadObject($dbcountry);
                  $language_id=$dbcountry->lid;
                  $time_zone_id=$dbcountry->time_zone_id;
                  if( $_REQUEST["cntry_lang_cd"]=='')
                    {
                      $_REQUEST["cntry_lang_cd"]= $language_id;
                    }
                  if( $_REQUEST["time_zone_id"]==0)
                    {
                      $_REQUEST["time_zone_id"]= $time_zone_id;
                    }
                    //echo "test".$values->indemailcheck;

                if(!empty($values->indemailcheck)){
                  $_REQUEST["contact_flag"]="E";
                }
                 if(!empty($values->busemailcheck)){
                  $_REQUEST["contact_flag"]="E";
                } 
	$_REQUEST['sendemail'] = "0";
	if(isset($Textboxvalues['notfn_email_check'])){
		$_REQUEST['sendemail'] = "1";
	}
 $q = adv_fields_save($_REQUEST, true, true, $country, false,"#__users", false);



 
 
        $database->setQuery($q);
        $database->query();




      $database->setQuery( "SELECT id FROM #__users"."\n WHERE username='".$username."'");
        $database->loadObject( $my_id );
        $my_user_id = $my_id->id;
		if(empty($my_user_id)){
			/* Below code block is used to write the reg failed log while registration into Notification System */
			  require_once($mosConfig_absolute_path.'/administrator/components/com_phpshop/classes/notifications/notificationfeeder.php');
			  $notificationfeeder=new notificationFeeder();
			  $notificationfeeder->createNotification('3',_IBO_REGISTRATION,str_replace("{user_name}",$username,_IBO_REGISTRATION_FAILED),$_REQUEST["contryid"]);
			/*end */
		}

           $sql_update_billing_flag_user="UPDATE #__users SET billing_id_flag='1' WHERE id=$my_user_id";
           $database->setQuery($sql_update_billing_flag_user);
           $database->query_batch();
$join_productid=explode("-",$_REQUEST[joinproductradio]);
$community_id = community_product($join_productid[0]);
if(!empty($community_id)){
  $_REQUEST["community_id"]=$community_id;
}
 $_REQUEST["enroll_type"]=$values->enrolType;
 $qExt='';
      // require_once($mosConfig_absolute_path.'/components/'ps_html.php');
       require_once( $mosConfig_absolute_path.'/administrator/components/com_phpshop/classes/ps_html.php' );
       $ps_html= new ps_html;
       $ci=$_REQUEST["contryid"];
      $ranks=$ps_html->get_default_rank($ci);


    $_REQUEST["rank_id"] =$ranks;
    $_REQUEST["reward_rank_id"]=$ranks;
      if($values->skipssn){
       $_REQUEST["korea_ssn_skip_flag"]=1;
      }
	if($currentUsers->gid == "3"){
		$_REQUEST["registered_from"]='Admin Site';
	}
	else if($_REQUEST['pibo'] == 'Y')
	{
		$_REQUEST["registered_from"]='Public Site';
	}
	else{
		$_REQUEST["registered_from"]='IBO Site';
	}	
       if(empty($_REQUEST["customer_leg"])){
               $vendor_id_set = "SELECT vendor_id FROM mambophil_pshop_vendor where vendor_country='".$country."'";
                    $database->setQuery($vendor_id_set);           
                    $database->loadObject($vendor_id_values);
          $_REQUEST["customer_leg"]=$ps_html->country_based_order_placment_leg($vendor_id_values->vendor_id);
       }

 $qExt = adv_fields_save($_REQUEST, true, true, $country, false,"#__adv_users", false);     
  //setting the tree placement options:
  require_once( $mosConfig_absolute_path. "/components/com_ibo/ibo_tree_maint.php");


  $pibo=$values->pibo;

  //this condition checks that the placemet option by sponsor is no

  $reg_kr = $values->reg_kr;

  if($values->korean_public_tree_flag=="1"){
    $pibo = "";
  }
  if($reg_kr == 1){
    $pibo = "";
  }
   //For PUBLIC IBO REGISTRATION SETTING THE TREE PLACEMENT Options

   if($pibo == 'Y' ){
	    $_SESSION["pibo"]='Y';
      $q= "SELECT id,default_direction FROM #__adv_users WHERE id = (SELECT id FROM #__users WHERE username='".$_SESSION["wwwuser"]->username."')";
      $database->setQuery($q);
      $database->query();
      if ($database -> getErrorNum()) {
          $errorMessage = '1 Database Error : ' .$database -> getErrorNum().' Database Message : ' .$database -> getErrorMsg();
      	  return $errorMessage;
      }
      $res = $database->loadObjectList();
      $leg_direction = $res[0]->default_direction;
      if(empty($leg_direction)) {
        $leg_direction = 'L';
      }
      $usertree=$_SESSION["wwwuser"]->username;
      if($leg_direction=='R'){
        $usertemptree=$usertree."-1";
        $SQL="SELECT user1 FROM mambophil_user_extended where user5 ='".$usertemptree."' AND user6 ='".$leg_direction."'";
        //echo $SQL;
        $database->setQuery($SQL);
        $database->loadObject($TREE_TEMP);
        $temp_tree=$TREE_TEMP->user1;
        //echo "TEST".$temp_tree;
        }
        if(($leg_direction=='R')&&(!empty($temp_tree))){
         $usertree=$temp_tree;
         $leg_direction="L";
        }
//         else{
//          $usertree=$usertree;
//          $leg_direction="L";
//         }

      require_once( $mosConfig_absolute_path. "/components/com_ibo/ibofunctions.php");

      $tree_ibo = getLastDownlineIBO($usertree, $leg='1', $leg_direction);

      $_REQUEST['insertleg'] = $leg_direction;
      $legs=explode('-',$tree_ibo);

      $SQL_SPONSORINFO="SELECT user6 as sponsorLeg,user8 as sponsorTP FROM mambophil_user_extended where user1='".$_SESSION["wwwuser"]->username."'";
          $database->setQuery($SQL_SPONSORINFO);

          $database->loadObject($SPONOSRVALUES);

      $leg=$leg_direction;
      $treeibo=$tree_ibo;
      $sponsorTP=$SPONOSRVALUES->sponsorTP;
      $sponsorLeg=$SPONOSRVALUES->sponsorLeg;
      $sponsor_number=$_SESSION["wwwuser"]->username;
      $plan_type="Standard";
      // End OF PUBLIC IBO REGISTRATION TREEE PLACEMENT
  }else{
  //setting tree informations

  if($values->placement_opt=="no"){

  if(isset($_SESSION["wwwuser"]->username)){
   $wwuser_name=$_SESSION["wwwuser"]->username;
   $usertree=$_SESSION["wwwuser"]->username;
  }else{
   $usertree=3000000;
   $wwuser_name=3000000;
  }
    $q= "SELECT id,default_direction FROM #__adv_users WHERE id = (SELECT id FROM #__users WHERE username='".$usertree."')";
      //echo $q;
      $database->setQuery($q);
      $database->query();
      if ($database -> getErrorNum()) {
          $errorMessage = '1 Database Error : ' .$database -> getErrorNum().' Database Message : ' .$database -> getErrorMsg();
      	  return $errorMessage;
      }
      $res = $database->loadObjectList();
      $leg_direction = $res[0]->default_direction;
      if(empty($leg_direction)) {
        $leg_direction = 'L';
      }
      if($leg_direction=='R'){
        $usertemptree=$usertree."-1";
        $SQL="SELECT user1 FROM mambophil_user_extended where user5 ='".$usertemptree."' AND user6 ='".$leg_direction."'";
        //echo $SQL;
        $database->setQuery($SQL);
        $database->loadObject($TREE_TEMP);
        $temp_tree=$TREE_TEMP->user1;
        //echo "TEST".$temp_tree;
        }
        if(($leg_direction=='R')&&(!empty($temp_tree))){
         $usertree=$temp_tree;
         $leg_direction="L";
        }
//         else{
//          $usertree=$usertree;
//          $leg_direction="L";
//         }
      require_once( $mosConfig_absolute_path. "/components/com_ibo/ibofunctions.php");

      $tree_ibo = getLastDownlineIBO($usertree, $leg='1', $leg_direction);

      $_REQUEST['insertleg'] = $leg_direction;
      $legs=explode('-',$tree_ibo);

      $SQL_SPONSORINFO="SELECT user6 as sponsorLeg,user8 as sponsorTP FROM mambophil_user_extended where user1='".$wwuser_name."'";
          $database->setQuery($SQL_SPONSORINFO);
          $database->loadObject($SPONOSRVALUES);

      $leg=$leg_direction;
      $treeibo=$tree_ibo;
      $sponsorTP=$SPONOSRVALUES->sponsorTP;
      $sponsorLeg=$SPONOSRVALUES->sponsorLeg;
      $sponsor_number=$wwuser_name;
      $plan_type="Standard";
  }else{

  $treeplacement=$values->treeplacement;
  $tree_parts = explode('-',$treeplacement);
  $treeibo = $tree_parts[0];

  $treeprovisions=explode(' ',$tree_parts[1]);
  $tree_track=$treeprovisions[0];
  $treeibo=$treeibo."-".$tree_track;
  $leg = $treeprovisions[1];

  //setting sponsor informations
  $sponsor_number = $values->sponsor;
  $sponsorTP = $values->sponsor_tp;
  $sponsorLeg = $values->sponsor_leg;
  $plan_type="Standard";

  if((empty($sponsorTP))&&(empty($sponsorLeg))){
    $SQL_SPONSORINFO1="SELECT user6 as sponsorLeg,user8 as sponsorTP FROM mambophil_user_extended where user1='".$sponsor_number."'";
          $database->setQuery($SQL_SPONSORINFO1);
          $database->loadObject($SPONOSRVALUES1);
          $sponsorTP = $SPONOSRVALUES1->sponsorTP;
          $sponsorLeg = $SPONOSRVALUES1->sponsorLeg;
   }

 }
  }


//code added by Luis Sanabra to ASSURE the new IBO will have the placement and Sponsor Information correctly.

 //Include files to calculate.
 require_once( $GLOBALS['mosConfig_absolute_path'] . '/components/com_ibo/SponsorPlacementCalculator.php' );
 require_once( $GLOBALS['mosConfig_absolute_path'] . '/components/com_ibo/ibofunctions.php' );
  //Create an array where to save uplne
  $arrayUpline = array();
  //Get IBO upline
  GetIBOUpline($treeibo, &$arrayUpline);
  //Calculate Tree Placements
  $upline = SponsorPlacementCalc::Calculate($treeibo,$leg,$arrayUpline);
   //Get Placement Information (Sponsor and Parent)
  $placementData = SponsorPlacementCalc::GetPlacementData($treeibo,$sponsor_number,$upline);
  if(!empty($placementData)){
    $leg = $placementData['parent']['leg'];
  	$sponsorTP = $placementData['sponsor']['placement'];
  	$sponsorLeg = $placementData['sponsor']['leg'];
	}else{
    $leg =$leg;
    $sponsorTP =$sponsorTP;
    $sponsorLeg =$sponsorLeg;
  }
  // End of Luis Code
  $ibo_closer = mosgetparam( $_REQUEST, 'ibo_closer', '');

  $myReturn = addIBOTree($my_user_id, $username, $plan_type, $leg, $sponsor_number, $sponsorTP, $sponsorLeg, $treeibo, false);
  $qExt .= $myReturn["SQL"];
    require_once( $GLOBALS['mosConfig_absolute_path']. "/administrator/components/com_phpshop/classes/ps_password.php");
    $ps_password = new ps_password();
       $qExt .=  $ps_password->generatePasswordlogMysqlInfo($my_user_id,$_REQUEST['password']);// get mysql query to create password log for a new user

      $database->setQuery( "SELECT aro_id from #__core_acl_aro "."\n WHERE value = '".$my_user_id."'");
      $database->loadObject( $userAroRow );
      $row_id = $userAroRow->aro_id;

// only insert if not already in table
    if (empty($row_id)) {
      $qExt .= "INSERT into  #__core_acl_aro (value, name, section_value, order_value, hidden)";
      $qExt .= "values ('". $my_user_id ."','" .$username ."','users','0','0');";
      $qExt .= "INSERT INTO #__core_acl_groups_aro_map (group_id, section_value, aro_id)";
      $qExt .= "SELECT '27', '', aro_id FROM #__core_acl_aro WHERE value = '" .$my_user_id ."'";
    }

    $database->setQuery($qExt);
	$database->query_batch();
	/*start: check whether adv_user added */

	$checkAdvQuery="SELECT COUNT(id) as cnt FROM mambophil_adv_users WHERE id=".$my_user_id;
	$database->setQuery($checkAdvQuery);
    $database->loadObject($advIdRes);
	if($advIdRes->cnt==0){
		/*adv users failed notification*/
		require_once($mosConfig_absolute_path.'/administrator/components/com_phpshop/classes/notifications/notificationfeeder.php');
		$notificationfeeder=new notificationFeeder();
		$notificationfeeder->createNotification('3',_IBO_REGISTRATION,str_replace("{user_name}",$username,_IBO_ADV_USER_FAILED),$_REQUEST["contryid"]);
		/*end*/	
	}
	/*end: check whether adv_user added */
	if(isset($Textboxvalues['notfn_email_check'])){
		$emailLogQuery = "INSERT INTO mambophil_email_check_log(user_id,username,country,check_type,check_status,check_action,module,created_by) VALUES (".$my_user_id.",'".$username."','".$country."','email mandatory','yes','add','ibo registration',".$my_user_id.")";
		$database->setQuery($emailLogQuery);
		$database->query();
	}
	if(isset($Textboxvalues['indemailcheck'])){
		$emailLogQuery = "INSERT INTO mambophil_email_check_log(user_id,username,country,check_type,check_status,check_action,module,created_by) VALUES (".$my_user_id.",'".$username."','".$country."','contact flag','yes','add','ibo registration',".$my_user_id.")";
		$database->setQuery($emailLogQuery);
		$database->query();
	}
   //sending email to admin about the tree placment issue
  $last_tree_id=$values->last_tree_id;
  $SQL_LASTID="SELECT COUNT(id) as counts FROM mambophil_ibo_position_lock where tree_placement='".$treeplacement."'";

  $database->setQuery($SQL_LASTID);
           $database->loadObject($SQL_LASTIDS);
if($SQL_LASTIDS->counts>=2){
       sendadminmailtreeissues($treeplacement,$username);
}

if($enrollcountry==1){
  $sql_update_closer="UPDATE #__user_extended SET user18='".$_REQUEST["ibo_closer"]."' WHERE user_id=$my_user_id";
    $database->setQuery($sql_update_closer);
    $database->query_batch();

 }
   if($values->skipssn==1){
       $sql_update_ssnskip="UPDATE #__adv_users SET korea_ssn_skip_flag='1' WHERE id=$my_user_id";
       $database->setQuery($sql_update_ssnskip);
       $database->query_batch();
   }


 if($enrol_type=='Business'){
 $sql_update_business="UPDATE #__adv_users SET business_name='".$_REQUEST["business_name"]."' WHERE id=$my_user_id";
    $database->setQuery($sql_update_business);
    $database->query_batch();
 }

 $ibo_remit = isset($_REQUEST["ibo_remit"]) ? $_REQUEST["ibo_remit"] : '';

 if($ibo_remit != ''){
 $sql_update_remit="UPDATE #__adv_users SET ibo_remit='".$ibo_remit."' WHERE id=$my_user_id";
    $database->setQuery($sql_update_remit);
    $database->query_batch();
 }


  if($ibo_remit != '' && $enrollcountry==10 ){
     $sql_update_remit_no="UPDATE #__user_extended SET user16='".$ibo_remit."' WHERE user_id=$my_user_id";
         $database->setQuery($sql_update_remit_no);
         $database->query_batch();
 }

  //end sending email option for admin
  //saving Product Information
//if($enrollcountry==1){
   require_once(CLASSPATH."ps_checkout.php");
   $ps_checkout = new ps_checkout;
   $termssource = "IBO Registration";
   $terms_user_id = $my_user_id;
   $source_reference = $username;
   $termscountry = "SELECT country_2_code FROM mambophil_pshop_country c 
                     INNER JOIN mambophil_pshop_vendor v ON v.vendor_country =c.country_3_code WHERE v.vendor_id= $enrollcountry";
                      $database->setQuery($termscountry);
                      $database->loadObject($termscountry_result);
                      $termscountry = $termscountry_result->country_2_code;
   $ps_checkout->logTermsandConditionsInfo($terms_user_id, $termssource,$source_reference,$termscountry);
//}


  $preferredcustomer=mosGetParam( $_REQUEST, 'preferredcustomer', '');
     if(!empty($preferredcustomer)){

        $sql_pre= "SELECT u.id,au.ibo_remit,au.community_id,u.fax,au.cell_phone FROM mambophil_users u
                    INNER JOIN mambophil_adv_users au on au.id=u.id
                    WHERE u.username='".$preferredcustomer."'";
                $database->setQuery($sql_pre);
                $database->loadObject($prefs);

              if($_REQUEST["cell_phone"]==""){
                   $cell_number= $prefs->cell_phone;
              }else{
                   $cell_number= $_REQUEST["cell_phone"];
              }

              if($_REQUEST["ibo_remit"]==""){
               $ibo_remit= $prefs->ibo_remit;
              }else{
               $ibo_remit= $_REQUEST["ibo_remit"];
              }

           $sql_update_prefereed="UPDATE #__adv_users SET ibo_upgrade_old_pc_id='".$prefs->id."',ibo_remit='".$ibo_remit."',community_id='".$prefs->community_id."',cell_phone='".$cell_number."' WHERE id=$my_user_id";
             $database->setQuery($sql_update_prefereed);
             $database->query_batch();

        $sql_update_prefereedd="UPDATE #__users SET fax='".$prefs->fax."' WHERE id=$my_user_id";
             $database->setQuery($sql_update_prefereedd);
             $database->query_batch();

          $sql_update_prefereed_status="UPDATE #__adv_users SET status_cd='C',status_chg_dt=NOW() WHERE id='".$prefs->id."' ";
             $database->setQuery($sql_update_prefereed_status);
             $database->query_batch();
       }



      if($values->nojointem=="0000"){
          $noitemflag=true;
          $sql_update_nojoin="UPDATE #__users SET free_acct_ind='1',block=0 WHERE id=$my_user_id";
          $database->setQuery($sql_update_nojoin);
          $database->query_batch();
         }

         $_SESSION["autoship_username"] = $_REQUEST['username'];
         $_SESSION["autoship_password"] = $_REQUEST['password'];

             $JOIN=$_REQUEST["joinproductradio"];
        $Auto_flag=explode("-",$JOIN);


      saveshippingaddress($_REQUEST,$values,$d,$country,$my_user_id,$username,$sponsor_number);
      $user_info_id=$_REQUEST["user_info_id"];
      $billing_address_info_id = $_REQUEST["billing_address_info_id"];

      //echo $user_info_id;
      //echo $billing_address_info_id;
       if(!empty($_POST["EmailOpt"])){
           $d["EmailOpt"]=$_POST["EmailOpt"];
           $_REQUEST["EmailOpt"]=$_POST["EmailOpt"];
          }  

      if(!$noitemflag){

         if($_REQUEST["showautoship"]=="showautoshi"){ // ($Auto_flag[1]==1) ||

           SaveAutoship($_REQUEST);
          }
       SaveProductsshoppingcart($_REQUEST,$values,$d,$country,$my_user_id,$username,$sponsor_number,$user_info_id,$billing_address_info_id);
      }else{

         require_once(CLASSPATH."ps_shopper.php");
         $ps_shopper = new ps_shopper;
          $cntry_join_cd=$values->enrollcontryid;

    // adds in the shopper group
    $ps_shopper->add($my_user_id,NULL, $username, NULL, $cntry_join_cd );

      }

    //Sending Email Information
    //print_r($values);
    if($values->enrollcontryid==83){
     sendKoreanIBOSMS($username,$my_user_id);
    }


   if(!empty($preferredcustomer)){
     //here goes duplicating of autoship and address
       duplicateaddressandautoships($my_user_id,$prefs->id);
   }


    //Sending Email Information
    require_once(CLASSPATH."ps_email.php");
    $ps_email = new ps_email;
    $srm_status = $ps_email->SendRegistrationEmails($my_user_id, $_REQUEST);
	if(!$srm_status){
        /*require_once($mosConfig_absolute_path.'/administrator/components/com_phpshop/classes/notifications/notificationfeeder.php');
		$notificationfeeder=new notificationFeeder(); 
		$notificationfeeder->createNotification('3',_IBO_REGISTRATION,str_replace("{user_name}", $username,_IBO_MAIL_NOT_SENT));*/
	  }

}


/**************************************************************************************************************
* Name   : SaveProductsshoppingcart
* Author :
* Date   :
* Desc   : Saves The product Inforamtion and payment informations
*
*
****************************************************************************************************************/


function SaveProductsshoppingcart($_REQUEST,$values,$d,$country,$my_user_id,$username,$sponsor_number,$user_info_id,$billing_address_info_id){

   global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY,$mosConfig_invoice_countries;

      $currentUser = $mainframe->getUser();

//productsave



    require_once(CLASSPATH."ps_shopper.php");
    $ps_shopper = new ps_shopper;
    $cntry_join_cd=$values->enrollcontryid;

    // adds in the shopper group
    $ps_shopper->add($my_user_id,NULL, $username, NULL, $cntry_join_cd );



    require_once(CLASSPATH."ps_checkout.php");
    $ps_checkout = new ps_checkout;

// Now place the order info
    $d = $_REQUEST;
    //var_dump($_REQUEST);
    //die();
    //print_r($d);
    //set form variables for navigation


    $d["plan_name"]="Standard-".$cntry_join_cd;
$vendor_ids = "SELECT vendor_id FROM mambophil_pshop_vendor where vendor_country='".$d["country"]."'";
                    $database->setQuery($vendor_ids);
                    $database->loadObject($vendor_id_val);

    if(($d["plan_name"]=="Standard-52") && (strpos($mosConfig_invoice_countries, $d["country"]) !== false && $vendor_id_val->vendor_id == 1 )){



			if(!empty($d["tax_id"])){

				//$sql = "INSERT into mambophil_ibo_rfc(user_id,RFC,StreetNumber,InternalNumber,StreetName,Area,PostalCode,City,State,Phone,CURP,RS)
				//VALUES ('".$my_user_id."','".addslashes($d["tax_id"])."','".addslashes($d["StreetNumber"])."','".addslashes($d["InternalNumber"])."','".addslashes($d["StreetName"])."','".addslashes($d["address_2"])."','".addslashes($d["zip"])."','".addslashes($_REQUEST["city"])."','".addslashes($d["state"])."','".addslashes($d["Phone"])."','".addslashes($d["curp"])."','".addslashes($d["RS"])."')  ";
				$sql_update = "UPDATE mambophil_adv_users set tax_id='".$d["tax_id"]."' where id='".$my_user_id."'";
				$database->setQuery($sql_update);
				$database->query_batch();

			}

           if($values->rfc_select==0){
               $rfc_id_value="-1";
           }
           else{

			   //Check if selected code exist and is on vigency
				$queryCFDI = "SELECT * FROM #__pshop_xmlcfdi_usingcfdi WHERE usingcfdi_id = '".$values->cfdi."' AND enabled = 'Y'";
				$database->setQuery($queryCFDI);
				$result = $database -> loadObjectList();
				if(count($result)==1){
					$sql_rfc = "INSERT into mambophil_ibo_rfc(user_id,RFC,StreetNumber,InternalNumber,StreetName,Area,PostalCode,City,State,Phone,CURP,RS,Type_RFC,email,usingcfdi_id, regimen_id, verification_4_0, deleted)
					VALUES ('".$my_user_id."','".strtoupper(addslashes(trim($values->RFC)))."','".strtoupper(addslashes(trim($values->StreetNumber)))."','".strtoupper(addslashes(trim($values->InternalNumber)))."','".strtoupper(addslashes(trim($values->StreetName)))."','".strtoupper(addslashes(trim($values->rfc_address_2)))."',
          '".strtoupper(addslashes(trim($values->rfc_zip)))."','".strtoupper(addslashes(trim($values->rfc_city)))."',
          '".strtoupper(addslashes(trim($values->rfc_state)))."','".strtoupper(addslashes(trim($values->Phone)))."','".strtoupper(addslashes(trim($values->CURP)))."',
          UPPER('".strtoupper(addslashes(trim($values->RS)))."'),'".strtoupper(addslashes(trim($values->Type_RFC)))."','".strtoupper(addslashes(trim($values->rfc_email)))."',
          ".$result[0]->usingcfdi_id." ,  ".strtoupper(addslashes($values->cfdi_regimen)).", 1,0
          )  ";
					//echo $sql_rfc;

					$database->setQuery($sql_rfc);
					$database->query();

					$sql_select_rfc="SELECT rfc_id FROM mambophil_ibo_rfc where user_id='".$my_user_id."' ORDER BY rfc_id DESC LIMIT 0,1";

					//echo  $sql_select_rfc;
					$database->setQuery($sql_select_rfc);
					$database->loadObject($sql_select_rfc_id);
					$rfc_id_value=$sql_select_rfc_id->rfc_id;
					
				}else{
					$_logger =& LoggerManager::getLogger('checkout.paymetradio.php-RFC IBO registration');
					$_logger->debug('RFC couldnt be stored at IBO registration process , CFDI is not valid.');
				}
			}


    }




    $d["checkout_next_step"] = "";
    $d["checkout_this_step"] = "99";
    $d["zone_qty"] = "";
    $d["option"] = "com_phpshop";
    $d["userid"] = $my_user_id;
    $d["page"] = "checkout.thankyou";
    $d["func"] = "checkoutprocess";
    $d["ship_to_info_id"] = $user_info_id;
    $d["bill_user_info_id"] = $billing_address_info_id;
    $d["shipping_rate_id"] = mosgetparam( $_REQUEST, 'shipping_rate_id', '');
    //$d["payment_method_id"] = 43;
    //$d["customer_note"] = "Automatic Enrollment Award";
    if ($_POST['customer_note']<>''){
    $d["customer_note"] = mosgetparam($_POST, 'customer_note');
    }else{
    $d["customer_note"] = "Automatic Enrollment Award";
    }
    $customer_leg = mosgetparam( $_REQUEST, 'customer_leg'); 
    if($customer_leg == null){
     require_once( $mosConfig_absolute_path.'/administrator/components/com_phpshop/classes/ps_html.php' );
     $ps_html= new ps_html;
     $customer_leg = $ps_html->country_based_order_placment_leg($vendor_id_val->vendor_id);
    } 
    $d["order_left_right"] = $customer_leg;
    $d["submit"] = "Confirm Order";
    $d["mosvisitor"] = "1";
    $d["entry_date"] = mosgetparam( $_POST, 'entry_date', '');
    $d["chk_cc"] = mosgetparam( $_REQUEST, 'chk_cc', 0);
    $d["chk_fc"] = mosgetparam( $_REQUEST, 'chk_fc', 0);
    $d["chk_db"] = mosgetparam( $_REQUEST, 'chk_db', 0);
	$d["exclude_ssunion"] = mosgetparam( $_POST, 'exclude_ssunion', '0');

	if(empty($d["entry_date"]))
	{
		$q = 'SELECT tz.Name FROM mambophil_pshop_country c
							INNER JOIN mysql.time_zone_name tz ON c.default_time_zone_id = tz.Time_zone_id
							WHERE c.country_3_code = "'.$d["country"].'";';
		$database->setQuery($q);
		$timezone = $database->loadResult();

		$datetime = new DateTime("now", new DateTimeZone($timezone));
		$d["entry_date"] = $datetime->format('Y-m-d');
	}

    $d["inscrip_ibo"]="1";
    $d["my_user_id"]=$my_user_id;
    $d["process_payment"] = $_POST["process_payment"];
    // set auth
    $auth["user_id"] = $my_user_id;
    $auth["username"] = $sponsor_number;
    $auth["perms"] = "shopper";
    $auth["first_name"] = $d["first_name"];
    $auth["last_name"] = $d["last_name"];
    $auth["country"] = $country;
    $auth["zip"] = $d["zip"];
    $auth["shopper_group_id"] = "8";
    $auth["default_shopper_group"] = "1";
    //$tempSession = $_SESSION["auth"];
    $d["auth"] = $auth;

    // set other misc
    $_SESSION["limitstart"] = 0;
    $_SESSION["keyword"] = "";
    $_SESSION["category_id"] = 0;
    $_SESSION["product_id"] = $values->join_plan;
    $_SESSION["last_page"] = "checkout.index";
    $_SESSION["coupon_discount"] = 0;
    $_SESSION["coupon_redeemed"] = false;
    $_SESSION["last_page"] = "checkout.index";
    $_SESSION["order_left_right"] = "L1";
    $_SESSION["autoship_ind"] = "0";
    $_SESSION["autoship_username"] = $_REQUEST['username'];
    $_SESSION["autoship_password"] = $_REQUEST['password'];
    if(!empty($sql_select_rfc_id->rfc_id)){
       $d["rfc_id"]=$rfc_id_value;
     }

//Setting the Multiple payment options for the Admin site
$SQL_TEMP_PAYMNET="SELECT COUNT(pid) as counts FROM mambophil_pshop_registration_temp_multiple_payment where username='".$username."'";
                         $database->setQuery($SQL_TEMP_PAYMNET);
                          $database->loadObject($rowcount);
                          $counts=$rowcount->counts;
       if((!empty($counts))){ 
         $paymentcc="DECODE(order_payment_number,'".ENCODE_KEY."') as CCNumber";
   $SQL_SELPAYMENT="SELECT pid,username,payment_method_id,creditcard_code,$paymentcc,FROM_UNIXTIME(order_payment_expire, '%m') AS order_payment_expire_month,
                    FROM_UNIXTIME(order_payment_expire, '%Y') AS order_payment_expire_year,order_payment_name,credit_card_code,payment_type,order_total, financial_months,payment_type,order_payment_date,order_confirm_number,order_payment_email,bank_name,cuotas,card_type FROM
                    mambophil_pshop_registration_temp_multiple_payment where username='".$username."'";
                     $database->setQuery($SQL_SELPAYMENT);
                    $Display=$database->loadObjectList();

   require_once(CLASSPATH . 'ps_payment_cart.php');
   $ps_payment_cart = new ps_payment_cart;
   $i=0;
   foreach($Display as $rows){
    if($rows->payment_type=="CC"){
      $ccdata[$i]["order_payment_number"] =$rows->CCNumber;
      $ccdata[$i]["enable_processor"] = "N";
      $ccdata[$i]["is_creditcard"] = "1";
      $ccdata[$i]["payment_cvv2_req"] = "Y";
      $ccdata[$i]["creditcard_code"] = $rows->creditcard_code;
      $ccdata[$i]["creditcard_name"] = "visa";
      $ccdata[$i]["order_payment_name"] =$rows->order_payment_name;
      $ccdata[$i]["order_payment_expire_month"] = $rows->order_payment_expire_month;
      $ccdata[$i]["order_payment_expire_year"] = $rows->order_payment_expire_year;
      $ccdata[$i]["credit_card_code"] = $rows->credit_card_code;
      $ccdata[$i]["is_creditcard"] = "1";
      $ccdata[$i]["financial_months"] = $rows->financial_months;
      $ccdata[$i]["chkadrs"] = "1"; //1 since in registration we save default billing address in cc billing
	  
	  $ccdata[$i]["cuotas"] = $rows->cuotas;

    $valReq = $values->requestvalues;
    if($valReq["ps_vendor_id"]==1){
      $ccdata[0]["bank_names"] = $rows->bank_name;
      $ccdata[0]["cardType"]   = $rows->card_type; //for insert on the table order payment
      $ccdata[0]["card_type"]  = $rows->card_type; //for call the proccess payment
    }
    
	 
    }
    elseif($rows->payment_type=="ES"){

    $ccdata[$i]["order_payment_number"] =$rows->CCNumber;
              $ccdata[$i]["enable_processor"] = "";
              $ccdata[$i]["is_creditcard"] = "0";
              $ccdata[$i]["payment_cvv2_req"] = "";
              $ccdata[$i]["creditcard_code"] = $rows->creditcard_code;
              $ccdata[$i]["creditcard_dname"] = "";
              $ccdata[$i]["order_payment_name"] =$rows->order_payment_name;
              $ccdata[$i]["payment_type"] = $rows->credit_card_code;
              $ccdata[$i]["order_payment_date"] =$rows->order_payment_date;
              $dates=explode("-",$rows->order_payment_date);
              $ccdata[$i]["order_payment_expire_month"] = $dates[1];
              $ccdata[$i]["order_payment_expire_year"] = $dates[0];
              $ccdata[$i]["order_payment_expire_date"] = $dates[2];
              $ccdata[$i]["is_creditcard"] = "0";
              $ccdata[$i]["financial_months"] = $rows->financial_months;
              $ccdata[$i]["auth_code"] = $rows->order_confirm_number;
             

    }
    elseif($rows->payment_type=="DB"){

    $ccdata[$i]["order_payment_number"] =$rows->CCNumber;
    $ccdata[$i]["enable_processor"] = "D";          
              
              $ccdata[$i]["payment_cvv2_req"] = "Y";
              $ccdata[$i]["creditcard_code"] = $rows->creditcard_code;
              $ccdata[$i]["creditcard_name"] = "visa";
              $ccdata[$i]["order_payment_name"] =$rows->order_payment_name;
              $ccdata[$i]["order_payment_expire_month"] = $rows->order_payment_expire_month;
              $ccdata[$i]["order_payment_expire_year"] = $rows->order_payment_expire_year;
              $ccdata[$i]["credit_card_code"] = $rows->credit_card_code;
              $ccdata[$i]["is_creditcard"] = "1";
              $ccdata[$i]["chkadrs"] = "1";
              

    }


	elseif($rows->payment_type=="FC"){

    $ccdata[$i]["order_payment_number"] =$rows->CCNumber;
    $ccdata[$i]["enable_processor"] = "F";
              $ccdata[$i]["is_creditcard"] = "1";
              $ccdata[$i]["payment_cvv2_req"] = "Y";
              $ccdata[$i]["creditcard_code"] = $rows->creditcard_code;
              $ccdata[$i]["creditcard_name"] = "visa";
              $ccdata[$i]["order_payment_name"] =$rows->order_payment_name;
              $ccdata[$i]["order_payment_expire_month"] = $rows->order_payment_expire_month;
              $ccdata[$i]["order_payment_expire_year"] = $rows->order_payment_expire_year;
              $ccdata[$i]["credit_card_code"] = $rows->credit_card_code;
              $ccdata[$i]["is_creditcard"] = "1"; 
			        $ccdata[$i]["financial_months"] = $rows->financial_months; 
              $ccdata[$i]["bank_names"] = $rows->bank_name; 
              $valReq = $values->requestvalues;
              if($valReq["ps_vendor_id"]==1){
                $ccdata[0]["cardType"]   = $rows->card_type; //for insert on the table order payment
                $ccdata[0]["card_type"]  = $rows->card_type; //for call the proccess payment
              }
             

        
    }
    elseif($rows->payment_type=="CH"){
              $check_details = explode("|", $rows->CCNumber);
              $check_accoutnnumber=$check_details[0];
              $ccdata[$i]["payment_chk_account_number"] =$check_details[0];
              $ccdata[$i]["payment_chk_routing_number"] =$check_details[1];
              $ccdata[$i]["payment_chk_account_type"] =$check_details[3];;
              $ccdata[$i]["payment_chk_type"] =$check_details[4];
              $ccdata[$i]["payment_chk_account_name"] =$rows->order_payment_name;
              $ccdata[$i]["enable_processor"] = "C";
             


   }else{
         if(($rows->payment_method_id==40)||($rows->payment_method_id==31) ||($rows->payment_method_id==114) ){

             $paydeatils = explode("|", $rows->CCNumber);
             $ccdata[$i]["order_payment_number"] = $paydeatils[0];
             $ccdata[$i]["order_payment_expire_month"]=$paydeatils[1];
            }
            $SQL_ENABLE_PROCESSOR="SELECT enable_processor FROM mambophil_pshop_payment_method where payment_method_id='".$rows->payment_method_id."'";
              $database->setQuery($SQL_ENABLE_PROCESSOR);
              $database->loadObject($SQ_ENABLE);
              $ccdata[$i]["enable_processor"]=$SQ_ENABLE->enable_processor;
              //for payu
              if($ccdata[$i]["enable_processor"] == "H"){
                $ccdata[$i]["payu_option"] = $rows->creditcard_code;
              }
               if($values->otherpaymentmethods == '38'){
                $ccdata[$i]['order_payment_name'] = $rows->order_payment_name;
                $ccdata[$i]['order_payment_expire_month'] = $rows->CCNumber;
                $ccdata[$i]['cc_bank_cd'] = $rows->bank_name;
                

               }

               if($values->otherpaymentmethods == '114'){
                $ccdata[$i]["wire_confirmation_number"]=$paydeatils[0];
                $ccdata[$i]["wire_confirmation_date"]=$paydeatils[1];
               }
              

   }
               $ccdata[$i]["payment_method_id"] = $rows->payment_method_id;
              $d["payment_method_id"]= $rows->payment_method_id;
              $ccdata[$i]["payment_amount"]=$rows->order_total;
              $ccdata[$i]["order_payment_email"] = $rows->order_payment_email;
              

              $ccdata["idx"] = $i+1;
              $d["ccdata"]=$ccdata;
              $ps_payment_cart->add($d["ccdata"]);
             
              $i++;
               
   }
 


      }else{
       //here setting the payment details
     if($values->payments=="ccpay"){
    //echo $SQL_PAYMENT_METHOD;
    $ccdata[0]["order_payment_number"] =$values->order_payment_number1."".$values->order_payment_number2."".$values->order_payment_number3."".$values->order_payment_number4;
              $ccdata[0]["enable_processor"] = "N";
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["payment_cvv2_req"] = "Y";
              $ccdata[0]["creditcard_code"] = $values->creditcard_code;
              $ccdata[0]["creditcard_name"] = "visa";
              $ccdata[0]["order_payment_name"] =$values->order_payment_name;
              $ccdata[0]["order_payment_expire_month"] = $values->order_payment_expire_month;
              $ccdata[0]["order_payment_expire_year"] = $values->order_payment_expire_year;
              $ccdata[0]["credit_card_code"] = $values->credit_card_code;
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["cuotas"] = $values->cuotas;
              $ccdata[0]["payment_method_id"] = $values->cc_paymentmethod_id;
              $d["payment_method_id"]= $values->cc_paymentmethod_id;
              $d["payment_method_id"]= $values->cc_paymentmethod_id;
              $ccdata[0]["financial_months"] = $requestvalues['financial_months'];
              $ccdata[0]["chkadrs"] = "1"; //1 since the cc billing addrs is default billing addrs
              $valReq = $values->requestvalues;
              if($valReq["ps_vendor_id"]==1){
                if(isset($valReq["bank_names_tc"])){
                  $ccdata[0]["bank_names"] = $valReq["bank_names_tc"];
                  $ccdata[0]["cardType"]   = $valReq["cardTypeTc"];//for insert on the table order payment
                  $ccdata[0]["card_type"]  = $valReq["cardTypeTc"]; //for call the proccess payment
                }
              }
          if($cntry_join_cd=="83"){
              $ccdata[0]["order_payment_number"] ="";
              $ccdata[0]["enable_processor"] = "N";
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["payment_cvv2_req"] = "Y";
              $ccdata[0]["creditcard_code"] = "";
              $ccdata[0]["creditcard_name"] = "";
              $ccdata[0]["order_payment_name"] ="";
              $ccdata[0]["order_payment_expire_month"] = "";
              $ccdata[0]["order_payment_expire_year"] = "";
            }
              $ccdata[0]["payment_amount"]=$values->order_total;
              $ccdata[0]["order_payment_email"]=$values->order_payment_email;
              $ccdata["idx"] = 1;
              $d["ccdata"]=$ccdata;
			  }elseif($values->payments=="fcpay"){

	$requestvalues = $values->requestvalues;
    $ccdata[0]["order_payment_number"] =$requestvalues['fc_order_payment_number1']."".$requestvalues['fc_order_payment_number2']."".$requestvalues['fc_order_payment_number3']."".$requestvalues['fc_order_payment_number4'];
              $ccdata[0]["enable_processor"] = "F";
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["payment_cvv2_req"] = "Y";
              $ccdata[0]["creditcard_code"] = $requestvalues['financialcard_code'];
              $ccdata[0]["creditcard_name"] = "visa";
              $ccdata[0]["order_payment_name"] =$requestvalues['fc_order_payment_name'];
              $ccdata[0]["order_payment_expire_month"] = $requestvalues['fc_order_payment_expire_month'];
              $ccdata[0]["order_payment_expire_year"] = $requestvalues['fc_order_payment_expire_year'];
              $ccdata[0]["credit_card_code"] = $requestvalues['fc_credit_card_code'];
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["financial_months"] = $requestvalues['financial_months'];
              $ccdata[0]["payment_method_id"] = $requestvalues['fc_paymentmethod_id'];
              $ccdata[0]["bank_names"] = $values->bank_names;
              $valReq = $values->requestvalues;
              if($valReq["ps_vendor_id"]==1){
                  $ccdata[0]["cardType"]   = 1;//for insert on the table order payment
                  $ccdata[0]["card_type"]  = 1; //for call the proccess payment
                  $ccdata[0]["bank_names"] = $_REQUEST['bank_names'];
              }
              $d["payment_method_id"]= $requestvalues['fc_paymentmethod_id'];  
              $ccdata[0]["payment_amount"]=$values->order_total;;
              $ccdata[0]["order_payment_email"]=$values->order_payment_email;
              $ccdata["idx"] = 1;
              $d["ccdata"]=$ccdata;
    }elseif($values->payments=="dbpay"){
    //echo $SQL_PAYMENT_METHOD;
    $ccdata[0]["order_payment_number"] =$values->db_order_payment_number1."".$values->db_order_payment_number2."".$values->db_order_payment_number3."".$values->db_order_payment_number4;
              $ccdata[0]["enable_processor"] = "D";
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["payment_cvv2_req"] = "Y";
              $ccdata[0]["creditcard_code"] = $values->db_creditcard_code;
              $ccdata[0]["creditcard_name"] = "visa";
              $ccdata[0]["order_payment_name"] =$values->db_order_payment_name;
              $ccdata[0]["order_payment_expire_month"] = $values->db_order_payment_expire_month;
              $ccdata[0]["order_payment_expire_year"] = $values->db_order_payment_expire_year;
              $ccdata[0]["credit_card_code"] = $values->db_credit_card_code;
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["payment_method_id"] = $values->db_cc_paymentmethod_id;
              $d["payment_method_id"]= $values->db_paymentmethod_id;
              $ccdata[0]["payment_amount"]=$values->order_total;
              $ccdata[0]["order_payment_email"]=$values->db_order_payment_email;
              $ccdata[$i]["chkadrs"] = "1";
              $ccdata["idx"] = 1;
              $d["ccdata"]=$ccdata;
    }
    elseif($values->payments=="kiccpay"){
    //echo $SQL_PAYMENT_METHOD;
    //echo "THIS CONDITION";
    $ccdata[0]["order_payment_number"] =$values->order_payment_number1."".$values->order_payment_number2."".$values->order_payment_number3."".$values->order_payment_number4;
              $ccdata[0]["enable_processor"] = "N";
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["payment_cvv2_req"] = "Y";
              $ccdata[0]["creditcard_code"] = $values->creditcard_code;
              $ccdata[0]["creditcard_name"] = "visa";
              $ccdata[0]["order_payment_name"] =$values->order_payment_name;
              $ccdata[0]["order_payment_expire_month"] = $values->order_payment_expire_month;
              $ccdata[0]["order_payment_expire_year"] = $values->order_payment_expire_year;
              $ccdata[0]["credit_card_code"] = $values->credit_card_code;
              $ccdata[0]["is_creditcard"] = "1";
              $ccdata[0]["cuotas"] = $values->cuotas;
              $ccdata[0]["payment_method_id"] = $values->cc_paymentmethod_id;
              $d["payment_method_id"]= $values->cc_paymentmethod_id;
              $ccdata[0]["payment_amount"]=$values->order_total;
              $ccdata[0]["order_payment_email"]=$values->order_payment_email;
              $ccdata[0]["financial_months"] = $values->financial_months;
              $ccdata["idx"] = 1;
              $d["ccdata"]=$ccdata;
              //print_r($d["ccdata"]);
    }


    elseif($values->payments=="easyswipe"){
    //echo $SQL_PAYMENT_METHOD;
    //echo "THIS CONDITION";
    $ccdata[0]["order_payment_number"] =$values->essw_order_payment_number1."".$values->essw_order_payment_number2."".$values->essw_order_payment_number3."".$values->essw_order_payment_number4;
              $ccdata[0]["enable_processor"] = "";
              $ccdata[0]["is_creditcard"] = "";
              $ccdata[0]["payment_cvv2_req"] = "";
              $ccdata[0]["creditcard_code"] = $values->essw_creditcard_code;
              $ccdata[0]["creditcard_name"] = "";
              $ccdata[0]["order_payment_name"] =$values->essw_order_payment_name;
              $ccdata[0]["is_creditcard"] = "";
              $ccdata[0]["cuotas"] = $values->cuotas;
              $ccdata[0]["payment_method_id"] = $values->essw_paymentmethod_id;
              $d["payment_method_id"]= $values->essw_paymentmethod_id;
              $ccdata[0]["payment_amount"]=$values->order_total;
              $ccdata[0]["financial_months"] = $values->essw_financial_months;
              $ccdata[0]["auth_code"] = $values->order_confirm_number;
              $ccdata[0]["payment_type"] = $values->payment_type;
              $ccdata[0]["order_payment_date"] =$values->essw_order_payment_date;
              $dates=explode("-",$values->essw_order_payment_date);
              $ccdata[0]["order_payment_expire_month"] = $dates[1];
              $ccdata[0]["order_payment_expire_year"] = $dates[0];
              $ccdata[0]["order_payment_expire_date"] = $dates[2];
              $ccdata[0]["is_creditcard"] = "0";
              $ccdata[0]["order_payment_email"]=$values->order_payment_email;
              $ccdata["idx"] = 1;
              $d["ccdata"]=$ccdata;
              //print_r($d["ccdata"]);
    }


    elseif($values->payments=="payck"){

                //echo $SQL_PAYMENT_METHOD;
              $ccdata[0]["payment_method_id"] = $values->check_paymentmethod_id;;
              $ccdata[0]["payment_chk_account_number"] =$values->payment_chk_account_number;
              $ccdata[0]["payment_chk_routing_number"] =$values->payment_chk_routing_number;
              $ccdata[0]["payment_chk_account_type"] =$values->payment_chk_account_type;
              $ccdata[0]["payment_chk_type"] =$values->payment_chk_type;
              $ccdata[0]["payment_chk_account_name"] =$values->payment_chk_account_name;
              $ccdata[0]["enable_processor"] = "C";
              $d["payment_method_id"]= $values->check_paymentmethod_id;
              $ccdata[0]["payment_amount"]=$values->order_total;
              $ccdata[0]["order_payment_email"]=$values->order_payment_email;
              $ccdata["idx"] = 1;
              $d["ccdata"]=$ccdata;

    }else{
    $ccdata[0]["payment_method_id"] = $values->otherpaymentmethods;
    $ccdata[0]["payment_amount"]=$values->order_total;

    $SQL_ENABLE_PROCESSOR="SELECT enable_processor FROM mambophil_pshop_payment_method where payment_method_id='".$values->otherpaymentmethods."'";
              //echo $SQL_ENABLE_PROCESSOR;
              $database->setQuery($SQL_ENABLE_PROCESSOR);
              $database->loadObject($SQ_ENABLE);
              $ccdata[0]["enable_processor"]=$SQ_ENABLE->enable_processor;

    if(($values->otherpaymentmethods==40)||($values->otherpaymentmethods==31 || $SQ_ENABLE->enable_processor == 'M')){
     $ccdata[0]["order_payment_number"] = $values->payment_bank_receipt;
     $ccdata[0]["order_payment_expire_month"]=$values->payment_bank_receipt_date;
    }
    if($SQ_ENABLE->enable_processor == 'H'){
      $ccdata[0]["payu_option"]=$values->payu_option;
    }

    if($values->otherpaymentmethods == '38'){
          $ccdata[0]['order_payment_name'] = $values->payment_bank_acct_name;
          $ccdata[0]['order_payment_expire_month'] =  $values->payment_bank_receipt_date;
          $ccdata[0]['cc_bank_cd'] = $values->cc_bank_cd;
    }
    if($SQ_ENABLE->enable_processor == 'J'){
      $ccdata[0]["wire_confirmation_number"]=$values->wire_confirmation_number;
      $ccdata[0]["wire_confirmation_date"]=$values->wire_confirmation_date;
    }
              $ccdata[0]["order_payment_email"]=$values->order_payment_email;
              $ccdata["idx"] = 1;
              $d["ccdata"]=$ccdata;
    }
      }
     //deleting temparory table data in case of multiple payment
   $sqlDelete_temp="DELETE FROM  mambophil_pshop_registration_temp_multiple_payment where username='".$username."'";
              //echo $sqlDelete_temp;
                 $database->setQuery($sqlDelete_temp);
                 $database->query();

 if($values->showshoppingcart=="showshopping"){
           $products_list=$values->shoppingcart;
          }
             unset($_SESSION["cart"]);
             require_once(CLASSPATH . 'ps_cart.php');
             $ps_cart = new ps_cart;

      $product_id = $values->join_plan;
	  
      $product_id = explode('-',$product_id);
      $product_id = $product_id[0];
      if(!empty($product_id)){
        $k["product_id"] = $product_id;
        $k["quantity"] = 1;
		$k["joinorder"] = 0;
        $ps_cart->add($k);
      }
	  unset($k["joinorder"]);

      $businesskit=$values->businessstareterkit;

      if(!empty($businesskit)){
        $k["product_id"] = $businesskit;
        $k["quantity"] = 1;
        $ps_cart->add($k);
      }


      // Promotion Product
      $promo_product_id = $values->onetimepromoitem;
      if(!empty($promo_product_id)){
        $k["product_id"] = $promo_product_id;
        $k["quantity"] = 1;
        $ps_cart->add($k);
      }

      $exclusive_product_id = $values->requestvalues["exlusiveproductradios"];
      if(!empty($exclusive_product_id)){
        $k["product_id"] = $exclusive_product_id;
        $k["quantity"] = 1;
        $ps_cart->add($k);
      }



      if(!empty($products_list)){
		$num=0;
        foreach ($products_list as $product_item) {
          $chunks = explode("|",$product_item);
          $product_id = $chunks[0];
          $product_quantity = $chunks[1];
          if(!empty($product_id) && !empty($product_quantity)){
            $k["product_id"] = $product_id;
            $k["quantity"] = $product_quantity;
			$k["pdtorder"] = $num;
            $ps_cart->validateQty=false;
            $ps_cart->add($k);
			unset($k["pdtorder"]);
			$num++;
          }
        }
      }

      //if user select to ship with order then insert into order table
      if($values->autoshiplikeorder=="yesautoshiplikeorder" && $values->showautoshipplan=="showautoshi"){
      $auto_products_list = $values->autoproduct_list;
      }
      if(!empty($auto_products_list)){
        foreach ($auto_products_list as $auto_product_item) {
          $auto_chunks = explode("|",$auto_product_item);
          $auto_product_id = $auto_chunks[0];
          $auto_product_quantity = $auto_chunks[1];
          if(!empty($auto_product_id) && !empty($auto_product_quantity)){
            $k["product_id"] = $auto_product_id;
            $k["quantity"] = $auto_product_quantity;
            $ps_cart->validateQty=false;
            $ps_cart->add($k);
          }
        }
      }
          $d["cart"]=$_SESSION["cart"];
          if(!empty($_POST["EmailOpt"])){
           $d["EmailOpt"]=$_POST["EmailOpt"];
          }      
          $d['plan_name']="Standard-".$cntry_join_cd;
          //$d['shipping_rate_id']="UPS%7CUPS+Ground%7C8.5%7C50";
          $d['shipping_rate_id']=$values->Shippingrate;
          $d['pick_up_code_val']=$values->pick_up_code_val;
          $d['order_type']=1;
          //$d["autoship_proc"]=1;


    if(($values->payments=="ccpay")&&($cntry_join_cd=='83')){
           $_REQUEST["payment_method_id"]=$values->cc_paymentmethod_id;
         }else{
          $_REQUEST["payment_method_id"]="";

         }
    $orderSatus = $ps_checkout->add($d);
    $_SESSION['reg_order_stat']=$d['order_status'];
    $_SESSION['order_id']=$_REQUEST["order_id"];
    if (! $orderSatus) {
    //echo "error";
//        HTML_ibo_maint::displayErrorMessage('System Error Placing Fast Start Item, please try again later '.$d["error"]);
//        return;
    }
    //this condition is used to change the block level of the IBO to 0 and Statsu to Active if the Orderstaus is paid
    IBOActivation($d['order_status'],$my_user_id,$cntry_join_cd); // set up the ibo as active if the order is confirm.
}


/**************************************************************************************************************
* Name   : SaveAutoship
* Author :
* Date   :
* Desc   : Saving the Autoship EnrollMent
*
****************************************************************************************************************/

function SaveAutoship($_REQUEST){
  global $mosConfig_mailfrom,$mosConfig_fromname,$CURRENCY_DISPLAY,$database;
  $autoship_id = saveDescription();
  //echo $autoship_id;
  $d = $_REQUEST;
  saveAutoshipItems($autoship_id);
  saveAutoshipShippingMethod($autoship_id,$d);
  savePaymentInfo($autoship_id,$_REQUEST);

  require_once(CLASSPATH . 'ps_html.php' );
  $ps_html = new ps_html;

//if($d["country"]=="MEX"){  
    require_once(CLASSPATH."ps_checkout.php");
    $ps_checkout = new ps_checkout;
    $termssource = "Autoship";
    $terms_user_id = $_SESSION["autoList"]["userid"];
    $source_reference = $autoship_id;
     $termscountrys = "SELECT country_2_code FROM mambophil_pshop_country c 
                       INNER JOIN mambophil_pshop_vendor v ON v.vendor_country =c.country_3_code WHERE v.vendor_country= '".$d["country"]."'";
     $database->setQuery($termscountrys);
     $database->loadObject($termscountry_results);
     $termscountrys = $termscountry_results->country_2_code;
    $ps_checkout->logTermsandConditionsInfo($terms_user_id, $termssource, $source_reference,$termscountrys );
 //}

  $community = $ps_html->get_community_id($_SESSION["autoList"]["userid"]);
  //send mail only if community id is not 3
    require_once(CLASSPATH."ps_email.php");
    $ps_email = new ps_email; 
  if($community!=3){       
    //Send Autoship Information
    $ps_email->SendAutoshipDetails($autoship_id, $_REQUEST);
  }else{
    $d["EmailOpt"]=$_REQUEST["EmailOpt"];
    if(!empty($d["EmailOpt"]) && $community == 3){
     $ps_email->SendAutoshipDetails_AT($autoship_id, &$d,'');
     enable_email_opt_AT($autoship_id,$d["EmailOpt"]);
    }
  }

  unset($_SESSION['autoList']);
}

function enable_email_opt_AT($autoship_id,$email_opt){
  
     global $database;
          
      $sql="INSERT INTO mambophil_at_autoship_email_subscription (autoship_id,email_opt) VALUES ($autoship_id,$email_opt)"; 
              $database->setQuery($sql);     
              $database->query_batch();
  }

function saveDescription($_REQUEST){
	
    global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY;
    
   // echo '<pre>'; print_r($_REQUEST); echo '</pre>';
    $username=mosgetparam( $_REQUEST, 'username', '');
    $autoship_description = mosgetparam( $_REQUEST, 'autoship_description', '[untitled]');
    $start_month = mosgetparam( $_REQUEST, 'start_month', '');
    $eday = mosgetparam( $_REQUEST, 'eday', 0);
	$ship_rate_id=mosgetparam( $_REQUEST, 'auto_shipping_rate_id', '');
	
     
    $autoInterval = mosgetparam( $_REQUEST, 'autoInterval', 1);


     $ship_rate_id=mosgetparam( $_REQUEST, 'auto_shipping_rate_id', '');
     $shipping_rate_id = urldecode($ship_rate_id);
     $autoship_description="Registration Via";
     $order_left_right="L";  // I don't think this assignment has any use here.
     $autoship_comments="At the time of enrollment";
     $ship_to_info_id=mosgetparam( $_REQUEST, 'username', '');
     $accept_terms_autoship = mosGetParam($_REQUEST,'chkTerm_autoship','0');
     $payment_type = mosGetParam($_REQUEST,'payments','');
      $ipaddress = ""; 
    	if (!empty($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
      else
        $ipaddress = $HTTP_SERVER_VARS['REMOTE_ADDR'];

    	$placed_user_id = $_SESSION['workauth']["user_id"];

    	$sqlEndDate = "";
    	if ($check_enddate) {
    		$sqlEndDate = "UNIX_TIMESTAMP('".$end_dt."')" . ",'";
    	} else {
    		$sqlEndDate = " '', '";
    	}
    if($_REQUEST['autoshiplikeorder']=="yesautoshiplikeorder"){
      $last_ship_date =  " UNIX_TIMESTAMP()";
    }
    else{
      $last_ship_date =  " 0";
    }
    //setting the startdate and eday of autoship for Join Auto enroll-->If the join product is Autoship

    $JOIN=$_REQUEST["joinproductradio"];
    $Auto_flag=explode("-",$JOIN);
//     if($Auto_flag[1]==1){
//         $startdate=date("Y-m-d");
//         $start_date=explode("-",$startdate);
//         $start_month=$start_date[1];
//         $eday=$start_date[2];
//     }
    //end

    if(empty($start_dt)){
    // make start date
    $start_year= date('Y');
    if($start_month<date('m')){
      $start_year++; // next year
    }
    //$start_dt = $start_year."-".$start_month."-".date('d');
    //changed for month date to be first day
    $start_dt = $start_year."-".$start_month."-01";
  }
    $order_left_right=$_REQUEST['customer_leg'];  // this field has the value of country based order placement leg or customer's optional leg 
    
   
    
    $database->setQuery( "SELECT id FROM #__users"."\n WHERE username='".$username."'");
        $database->loadObject( $my_id );
        $user_id = $my_id->id;
        $_SESSION['autoList']['userid'] = $user_id;
    if($_REQUEST["shippingaddress"]=="newship" || $_REQUEST["shippingaddress"]=="center"){
        $d["address_type"]="st";
        $qsh = "SELECT user_info_id from #__pshop_user_info where user_id = ".$user_id." and address_type = '".$d["address_type"]."'";
      }else{
        $d["address_type"] = "BT";
        $qsh = "SELECT user_info_id from #__users where id = ".$user_id." and address_type = '".$d["address_type"]."'";
      }

       $database->setQuery($qsh );
       $database ->loadObject($dbuser4);
       $ship_to_info_id= $dbuser4->user_info_id; 
      $autoship_paym_insert = null;  
   if($_REQUEST["otherpaymentmethods"] > 0) { // for paypal payment registration with autoship selected
     $pay_methods = getPaymentMethodDetails($_REQUEST["otherpaymentmethods"]);
     $selected_otherpayment_code =  $pay_methods->payment_method_code;
     if($selected_otherpayment_code == "PSTD"){
       $autoship_paym_insert = "paypal";
       $eday = date('d');
     }
   }
   if( $_REQUEST["showautoship"] == "showautoshi" && $_REQUEST["payments"] =="ccpay" && $_REQUEST["chk_payu_subscr"] == "on") {
    $autoship_paym_insert = "payu_subscr";
   }
  
   $pick_up_code_val = mosgetparam( $_REQUEST, 'pick_up_code_val', '');
  
   $qExt .= "INSERT INTO #__pshop_autoship";
   $qExt .= " (user_id, user_info_id, order_status, cdate, mdate, eday,last_shipped_dt, start_dt, end_dt, ship_method_id, "
         .  " customer_note, leftright_order, ip_address, placed_user_id, autoship_paym, autoship_description, autoship_comments, register_id, lastupdateDate,autoship_run_frequency,accep_terms_cond, dt_terms_cond, pick_up_code_val) ";
   $qExt .= "VALUES ('";
   $qExt .=  $user_id. "','"; // Working IBO
   $qExt .= $ship_to_info_id . "','";
   $qExt .= "I" . "',";
   $qExt .= "UNIX_TIMESTAMP()" . ",";
   $qExt .= "UNIX_TIMESTAMP()" . ",";
   $qExt .= $eday . ",";
   $qExt .= $last_ship_date.",";
   $qExt .= "UNIX_TIMESTAMP('".$start_dt."')" . ",";
   $qExt .= $sqlEndDate;
   $qExt .= $ship_rate_id. "','";
   $qExt .= " " . "','";
   $qExt .= $order_left_right . "','";
   $qExt .= $ipaddress . "','";
   $qExt .= $placed_user_id . "','";
   $qExt .= $autoship_paym_insert . "','";
   $qExt .= $autoship_description . "','";
   $qExt .= $autoship_comments . "','";
   $qExt .= $id."',";
   $qExt .= "UNIX_TIMESTAMP(),";
   $qExt .=  $autoInterval.",";
   $qExt .= $accept_terms_autoship. ",";
   $qExt .= "NOW(),";
   $qExt .= "'".$pick_up_code_val. "')";

   //$_logger->debug('SQL for the create new Autoship = '.$qExt);
    $database->setQuery($qExt);

	$database->query_batch();
  if ($database -> getErrorNum()) {
	  /* autoship not saved Notification System */
	  require_once($mosConfig_absolute_path.'/administrator/components/com_phpshop/classes/notifications/notificationfeeder.php');
	  $notificationfeeder=new notificationFeeder();
	  $notificationfeeder->createNotification('3',_IBO_REGISTRATION,str_replace("{user_name}",$username,_IBO_AS_NOT_SAVED),$_REQUEST["contryid"]); 
	/*end */
     $errorMessage = 'Database Error : ' .$database -> getErrorNum().' Database Message : ' .$database -> getErrorMsg();
	   //$_logger->error('Issue inserting batch insert - '.$errorMessage);
     if ($database->getErrorNum() != "01062") {
        return $stndErrorMessage;
     }
  }



$qSel = "SELECT MAX(autoship_id) AS autoship_id FROM #__pshop_autoship WHERE user_id=".$user_id;
    //echo $qSel;
    $database->setQuery($qSel);
    $database->loadObject($objLastIdx);
    $ret_autoship_id = $objLastIdx->autoship_id;

  $eday_log="INSERT INTO mambophil_pshop_autoship_log (user_id,autoship_id,log_description,log_time,admin_login_id)
                values($user_id,$ret_autoship_id,'Autoship created on new account register',now(),$user_id)" ;
        $database->setQuery($eday_log);
        $database->query();

  if($_REQUEST['autoshiplikeorder']=='yesautoshiplikeorder' && $_REQUEST['showautoship'] =='showautoshi'){
    $daily_run="INSERT INTO mambophil_pshop_autoship_daily_run
        (autoship_id, run_day,run_month, run_year, run_status, created_dt) values ($ret_autoship_id,EXTRACT(DAY FROM CURDATE()),
        EXTRACT(month FROM CURDATE()),EXTRACT(year FROM CURDATE()),'2',UNIX_TIMESTAMP(NOW()))";
    $database->setQuery($daily_run);
    $database->query();
  }
  return $ret_autoship_id;
}


function saveAutoshipItems($autoship_id){
   global $database,$mainframe;
  //echo "test".$autoship_id;
     $JOIN=$_REQUEST["joinproductradio"];
    //$Auto_flag=explode("-",$JOIN);

      //$product_id=$_REQUEST["autoproduct"];
      //$quantity="1";

 $products_list = mosgetparam( $_REQUEST, 'autoproduct_list', null);
 $i=0;
	foreach($products_list as $product_item){
	      $chunks = explode("|",$product_item);
	      //echo "ProductId:".$chunks[0]."Qty:".$chunks[1]."<br>";
				$productos[$i]['product_id'] = $chunks[0];
				$productos[$i]['quantity'] = $chunks[1];
				$productos[$i]['description']="";
				$productos[$i]['volume_val']="";
				$i++;

      $BVcalc="SELECT volume,vendor_id FROM mambophil_pshop_product where product_id =$chunks[0]";
                     $database->setQuery($BVcalc);
                     $database->loadObject($BVcalcs);
                     $bv_perproduct=$BVcalcs->volume;
                     $bv_perproduct=$chunks[1] * $bv_perproduct;
                     $bv_perproducts += $bv_perproduct;
	}
	  //echo $bv_perproducts;
	//check for promotion item
	$sqlUser="SELECT ADV.cntry_join_cd CNTRY FROM #__users AS USR
                INNER JOIN #__pshop_autoship a ON USR.id=a.user_id
                INNER JOIN #__adv_users ADV on ADV.id=USR.id
                WHERE a.autoship_id=".$autoship_id;

  	$database->setQuery($sqlUser);
  	$database->loadObject($rowUser);
  	if ($database -> getErrorNum()) {
  		echo "Error:".$database -> stderr();
  	}
  	$usr_cntry_id=$rowUser->CNTRY;
	  $_SESSION['autoList']['bv'] = $bv_perproducts;
    if($bv_perproducts>=80){
    $sqlPromo="SELECT p.product_id, aprom.promo_product_quantity, p.product_name, p.product_desc,
            p.vendor_id, p.volume, p.product_weight, pp.product_price, pp.product_currency, pc.currency_name
            FROM `mambophil_pshop_autoship_promotion` AS aprom
            LEFT JOIN mambophil_pshop_product AS p ON p.product_id = aprom.promo_product_id
            LEFT JOIN mambophil_pshop_product_price AS pp ON pp.product_id = aprom.promo_product_id
            LEFT JOIN mambophil_pshop_currency AS pc ON pc.currency_code = pp.product_currency
            WHERE aprom.promo_country_id=$usr_cntry_id AND aprom.promo_active_fl=1
					  AND CURDATE()>=aprom.promo_start_dt AND CURDATE()<=aprom.promo_end_dt
                ORDER BY promo_grace_period";
    $database->setQuery($sqlPromo);
    $database->loadObject($resPromo);
  	if ($database -> getErrorNum()) {
  		echo "Error:".$database -> stderr();
  	}
	}
	if($resPromo){
  	  //Eligible for promotion..
       			$productos[$i]['quantity']=$resPromo->promo_product_quantity;
				$productos[$i]['product_id']=$resPromo->product_id;
				$productos[$i]['description']=$resPromo->product_desc;
				$productos[$i]['volume_val']=$resPromo->volume;
				$i=$i+1;
    }

	for ($i=0;$i<count($productos);$i++) {
      if (!isset($productos[$i]['autoship_item_id'])){
        $product_id	= $productos[$i]['product_id'];
        $quantity = $productos[$i]['quantity'];
        $order_status = "A";
        $cdate = "UNIX_TIMESTAMP()";
        $mdate = "UNIX_TIMESTAMP()";
        $product_attribute = "";
      //echo "aravind".$autoship_id;
  			$qExt .= "INSERT INTO #__pshop_autoship_item ";
  		  $qExt .= " (autoship_id        , 						product_id         ,							product_quantity,   ";
  			$qExt .= "	order_status       , 						cdate              ,							mdate,              ";
  			$qExt .= "	product_attribute ) ";
  		  $qExt .= "VALUES (";
  		  $qExt .= $autoship_id. ",";
  		  $qExt .= $product_id. ",";
  			$qExt .= $quantity. ",";
  		  $qExt .= "'" .$order_status. "',";
  		  $qExt .= $cdate. ",";
  		  $qExt .= $mdate. ",";
  		  $qExt .= "'".$product_attribute."'); ";
  		  $bln=1;


  	  }
	}
	  $database->setQuery($qExt);
	      $database->query_batch();
}

function saveAutoshipShippingMethod($autoship_id,$d) {
  global $database;

  if ($d["country"] == "AUS" || $d["country"] == "NZL"){
	$database->setQuery("SELECT FROM_UNIXTIME(cdate,'%Y-%m-%d') AS cdate FROM #__pshop_autoship WHERE autoship_id=".$autoship_id);
	$database->loadObject($asCreated);
	$createdate = $asCreated->cdate;
	$ascDate = new DateTime($createdate);
	$ascDate = $ascDate->format('Y-m-d H:i:s');
	$startdate = new DateTime("2020-10-23");
	$startdate = $startdate->format('Y-m-d H:i:s');
	$enddate = new DateTime("2020-12-31 23:59:59");
	$enddate = $enddate->format('Y-m-d H:i:s');
	$asProduct=array();
	if($startdate <= $ascDate && $ascDate <= $enddate){	
	$checkPdtQuery="SELECT pdt.product_id AS product_id FROM #__pshop_product  AS pdt JOIN #__pshop_product_price AS pp ON pdt.product_id=pp.product_id WHERE pdt.vendor_id=18  AND pdt.autoship_flag=1 AND pdt.product_publish='Y' AND pp.product_price >= 140";
	$database->setQuery($checkPdtQuery);
	$checkPdtRes=$database->loadObjectList();
	$asProduct=array();
	foreach($checkPdtRes as $cp){
		array_push($asProduct,$cp->product_id);
	}
	}
    require_once(CLASSPATH . 'ps_shipping_method.php' );
    require_once(CLASSPATH . 'ps_checkout.php' );
    $d['registration_autoship'] = true;
    if($d['autoproduct_list'] != 'null' && $d['autoproduct_list'] != 'undefined') {
    //get country short code
    $country_sql = "SELECT country_2_code AS short_code FROM mambophil_pshop_country WHERE country_3_code='".$d["country"]."'";
    $database->setQuery($country_sql );
    $database ->loadObject($countrycode);
    $shortcountrycode = $countrycode->short_code;

    $shortcode = "";
    if( $shortcountrycode=="US"
        || $shortcountrycode=="PR"
        || $shortcountrycode=="DO"
  	    || $shortcountrycode=="CA"
  		  || $shortcountrycode=="AU"
  		  || $shortcountrycode=="NZ"
         ) {
      $shortcode =  $shortcountrycode;
     }

	$fs=0;
    foreach ($d['autoproduct_list'] as $autoship_product) {
      $chunks = explode("|", $autoship_product);
      $product_id = $chunks[0];
      $quantity = $chunks[1];
      $autoship_weight_subtotal = ps_shipping_method::get_weight($product_id, $shortcode) * $quantity;
      $autoship_weight_total += $autoship_weight_subtotal;
	  if($startdate <= $ascDate && $ascDate <= $enddate){	
		  if(in_array($product_id, $asProduct)){
			$fs=1;
		  }
	  }
    }
    $d["autoship_weight"] = $autoship_weight_total;
    $d["default_autoship_method"] = $country_autoship_method;
    }

    $d["default_autoship_method"] = urldecode($d['shipping_rate_id']);
    $ps_checkout = new ps_checkout;
    $vShiprateId = $d['default_autoship_method'];
	if($d["country"] == "NZL" && $fs==1){
		$vShiprateId="Special Shipping|Free Shipping|0|3656";
	}
  }else{
    // Not a radio, but a hidden input
	$radioShiprate=mosgetparam( $_REQUEST, 'auto_shipping_rate_id', '');


 //
  if($d["country"] == "MEX"){
    $product_total_mex = 0;
    require_once(CLASSPATH . 'ps_product.php');
     $ps_product = new ps_product();
    foreach ($d['autoproduct_list'] as $autoship_product) {
      $chunks = explode("|", $autoship_product);
      $product_id = $chunks[0];
      $quantity = $chunks[1];
      $price = $ps_product->get_adjusted_attribute_price($product_id, "", $d);
      $product_total_mex +=  ($price["product_price"] * $quantity);
    }

    // if($product_total_mex>=14000){
    //   //echo $d["shipping_rate_id"];
    //   if(stripos($d["shipping_rate_id"], 'Free')!== false) {
    //     $free_elibile = true;
    //     $where_free= "free";
    //   }elseif(stripos($d["shipping_rate_id"], 'CDMX')!== false) {
    //     $free_elibile = true;
    //     $where_free = "CDMX";
    //   }elseif(stripos($d["shipping_rate_id"], 'Guadalajara')!== false){
    //     $free_elibile = true;
    //     $where_free= "Guadalajara";
    //   }elseif(stripos($d["shipping_rate_id"], 'Monterrey')!== false){
    //     $free_elibile = true;
    //     $where_free= "Monterrey";
    //   }
    //   if($free_elibile){
    //       $mexico_shipping_method = "SELECT sr.*,cr.* FROM `mambophil_pshop_shipping_rate` sr 
    //                       INNER JOIN mambophil_pshop_shipping_carrier cr ON cr.shipping_carrier_id= sr.shipping_rate_carrier_id
    //                       WHERE shipping_rate_country ='MEX;' AND sr.shipping_rate_name LIKE '%$where_free%' AND sr.shipping_rate_value='0.0000' AND sr.shipping_rate_zip_start=0";
    //                       $database->setQuery($mexico_shipping_method);
    //                       //echo $mexico_shipping_method;
    //           $database->loadObject($mexico_shipping_methods);

            
    //           $mex_free_total_shipping_handling =0;
    //           $free_shipping_name = urlencode($mexico_shipping_methods->shipping_carrier_name."|".$mexico_shipping_methods->shipping_rate_name."|".$mex_free_total_shipping_handling."|".$mexico_shipping_methods->shipping_rate_id);           
    //           $radioShiprate = $free_shipping_name;
    //   }
    // } 
  }

        //echo $radioShiprate;
	      //exit;
  	//$radioShiprate = mosgetparam( $_REQUEST, 'auto_shipping_rate_id', '');

  	// $vShiprateId = str_replace("%7C", "|", $radioShiprate);
  	// $vShiprateId = str_replace("+", " ", $vShiprateId);
  	$vShiprateId = urldecode($radioShiprate);
	
  }
  	$qExt = '';
  	$qExt .= "UPDATE #__pshop_autoship";
    $qExt .= " SET ship_method_id = '$vShiprateId' ";
    $qExt .= " WHERE autoship_id = ".$autoship_id;

  	$qExt .= "; UPDATE #__pshop_autoship";
  	$qExt .= " SET order_status = 'A' ";
  	$qExt .= " WHERE autoship_id = ".$autoship_id . ";";
  	$_REQUEST['autoshipstatus']='A';

  	//Commit the insert.
    //$_logger->debug('SQL for the create new Autoship = '.$qExt);
  	$database->setQuery($qExt);
  	$database->query_batch();

    #check if registration has autoship
    $sqlAutType = "SELECT COUNT(autoship_id) as total FROM mambophil_pshop_autoship 
    WHERE autoship_id=$autoship_id 
    AND autoship_description='Registration Via' 
    AND autoship_comments='At the time of enrollment' AND 
    order_status ='A'";
    $database->setQuery($sqlAutType);
    $database->loadObject($objAutoDesc);

    if($objAutoDesc->total >=1){
      $sqlUserInfo="SELECT state FROM mambophil_pshop_user_info WHERE user_info_id =".$_REQUEST['user_info_id']." AND zip between '80000' AND '82996' LIMIT 1";
      $database->setQuery($sqlUserInfo);
      $database->loadObject($rstUserInfo);

      if($rstUserInfo->state=='SIN'){

        #check weight up to 5kg
        $sqlWeight="SELECT 
          sum(( ai.product_quantity * p.product_weight )) /1000 autoshipWeight 
          FROM mambophil_pshop_autoship a 
          LEFT JOIN mambophil_pshop_autoship_item ai ON ai.autoship_id = a.autoship_id 
          LEFT JOIN mambophil_pshop_product p ON p.product_id = ai.product_id 
          WHERE 1 AND a.autoship_id IN ($autoship_id)
          GROUP BY a.autoship_id";
        $database->setQuery($sqlWeight);
        $database->loadObject($rstWeight);

        if($rstWeight->autoshipWeight < 5.0){
          $sqlNewShipSin="SELECT CONCAT('Paquetería|Envio Especial Sinaloa|0|',shipping_rate_id) shipSinaloa, params FROM mambophil_pshop_shipping_rate WHERE shipping_rate_name='Envio Especial Sinaloa' ORDER BY shipping_rate_id DESC LIMIT 1";
          $database->setQuery($sqlNewShipSin);
          $database->loadObject($rstNewShipSin);
  
          $shipp_params = json_decode($rstNewShipSin->params);
          $now          = date('Y-m-d H:i:s');
          $tNow         = strtotime($now);
          if($shipp_params->exp_date_spec_ship_sinaloa != null){
            $exp_date = strtotime($shipp_params->exp_date_spec_ship_sinaloa);
            if($tNow < $exp_date){
              $upSinaloa = "UPDATE mambophil_pshop_autoship 
              SET ship_method_id = '$rstNewShipSin->shipSinaloa' 
              WHERE autoship_id = ".$autoship_id;
              $database->setQuery($upSinaloa);
              $database->query_batch();
            }
          }
        }
      }
    }


  	$rtn = false;
    if ($database -> getErrorNum()) {
       $errorMessage = 'Database Error : ' .$database -> getErrorNum().' Database Message : ' .$database -> getErrorMsg();
  	  // $_logger->error('Issue inserting batch insert - '.$errorMessage);
       if ($database->getErrorNum() != "01062") {
          return $stndErrorMessage;
       }
    }else{
       $rtn = true;
    }

    return $rtn;
}

function savePaymentInfo($autoship_id,$_REQUEST) {
	global $database, $mosConfig_absolute_path,$mainframe;
	//echo $autoship_id;
	require_once(CLASSPATH . 'ps_payment_method.php');
    $ps_payment_method = new ps_payment_method;
	$order_payment_name = mosgetparam( $_REQUEST, 'order_payment_name', 0);
	$order_payment_method_id= mosgetparam( $_REQUEST, 'cc_paymentmethod_id', 0);
    $payU_subscr_enabled_vendors =  $ps_payment_method->getVendorListOfPayUSubscription(); 
    if(in_array($_REQUEST['ps_vendor_id'],$payU_subscr_enabled_vendors) &&  $_REQUEST['showautoship'] == "showautoshi" &&  $_REQUEST['payments'] == "ccpay" && $_REQUEST['chk_payu_subscr'] == "on" ){  
      $payu_subscr_method_id =  $ps_payment_method->getPayUSubscriptionPaymentId($_REQUEST['ps_vendor_id']); 
      if($payu_subscr_method_id != ""){
        $order_payment_method_id = $payu_subscr_method_id;
      }
    }
	$order_payment_number1 = mosgetparam( $_REQUEST, 'order_payment_number1', 0);
	$order_payment_number2 = mosgetparam( $_REQUEST, 'order_payment_number2', 0);
	$order_payment_number3 = mosgetparam( $_REQUEST, 'order_payment_number3', 0);
	$order_payment_number4 = mosgetparam( $_REQUEST, 'order_payment_number4', 0);
	$credit_card_code = mosgetparam( $_REQUEST, 'credit_card_code', 0);
	$order_payment_expire_month = mosgetparam( $_REQUEST, 'order_payment_expire_month', 0);
	$order_payment_expire_year = mosgetparam( $_REQUEST, 'order_payment_expire_year', 0);
//	$autoship_id = mosgetparam( $_REQUEST, 'autoshipid', 0);
	$creditcard_code = mosgetparam( $_REQUEST, 'creditcard_code', 0);
	$financial_months=0;
	$accept_terms = mosgetparam($_REQUEST,'chk_cc','0');
	if($_SESSION['auto_credit_card']['flag'] == 1){
   $order_payment_method_id = $_SESSION['auto_credit_card']["payment_method_id"];
   $order_payment_name = $_SESSION['auto_credit_card']["order_payment_name"];
   $order_payment_number1 = $_SESSION['auto_credit_card']["order_payment_number1"];
   $order_payment_number2 = $_SESSION['auto_credit_card']["order_payment_number2"];
   $order_payment_number3 = $_SESSION['auto_credit_card']["order_payment_number3"];
   $order_payment_number4 = $_SESSION['auto_credit_card']["order_payment_number4"];
   $credit_card_code = $_SESSION['auto_credit_card']["credit_card_code"];
   $creditcard_code = $_SESSION['auto_credit_card']["creditcard_code"];
   $order_payment_expire_month = $_SESSION['auto_credit_card']["order_payment_expire_month"];
   $order_payment_expire_year = $_SESSION['auto_credit_card']["order_payment_expire_year"];
  }

	$status = mosgetparam( $_REQUEST, 'status', 'D');
	$chkadrs = mosgetparam( $_REQUEST, 'chkadrs', '');
	$billing_info_id = mosgetparam( $_REQUEST, 'billing_info_id', '');
	$order_payment_number = $order_payment_number1.$order_payment_number2.$order_payment_number3.$order_payment_number4;
	$payment_type="CC";

	//for US check payments
  if(($_REQUEST["payments"]=="payck") && ($_REQUEST["ps_vendor_id"]=="9")){
	  $order_payment_method_id= mosgetparam( $_REQUEST, 'check_paymentmethod_id', 0); 
	  $order_payment_number1 = mosgetparam( $_REQUEST, 'payment_chk_account_number', 0);  
    $order_payment_number2 = mosgetparam( $_REQUEST, 'payment_chk_routing_number', 0); 
    $order_payment_number3 = mosgetparam( $_REQUEST, 'payment_chk_account_type', 0);
	  $order_payment_number4 = mosgetparam( $_REQUEST, 'payment_chk_type', 0);
	  $order_payment_number = $order_payment_number1."|".$order_payment_number2."||".$order_payment_number3."|".$order_payment_number4;
  	$order_payment_name = mosgetparam( $_REQUEST, 'payment_chk_account_name', 0);
    $creditcard_code = "CHECK";
    $payment_type = "CH";
  }elseif( $_REQUEST["payments"] == "fcpay" ){
	  $order_payment_method_id= mosgetparam( $_REQUEST, 'payment_method_id', 0);
	  $order_payment_number1 = mosgetparam( $_REQUEST, 'fc_order_payment_number1', 0);
	  $order_payment_number2 = mosgetparam( $_REQUEST, 'fc_order_payment_number2', 0);
	  $order_payment_number3 = mosgetparam( $_REQUEST, 'fc_order_payment_number3', 0);
	  $order_payment_number4 = mosgetparam( $_REQUEST, 'fc_order_payment_number4', 0);
	  $order_payment_number = $order_payment_number1.$order_payment_number2.$order_payment_number3.$order_payment_number4;
	  $credit_card_code = mosgetparam( $_REQUEST, 'fc_credit_card_code', 0);
	  $creditcard_code = mosgetparam( $_REQUEST, 'financialcard_code', 'VISA');
	  $order_payment_name = mosgetparam( $_REQUEST, 'fc_order_payment_name', '');
	  $financial_months = mosgetparam( $_REQUEST, 'financial_months', 0);
	  $order_payment_expire_month = mosgetparam( $_REQUEST, 'fc_order_payment_expire_month', 0);
	  $order_payment_expire_year = mosgetparam( $_REQUEST, 'fc_order_payment_expire_year', 0);
	  $payment_type="CC";
    $accept_terms = mosgetparam($_REQUEST,'chk_fc','0');

  }
   if($_REQUEST["otherpaymentmethods"] > 0) {
     $pay_methods = getPaymentMethodDetails($_REQUEST["otherpaymentmethods"]);
     $selected_otherpayment_code =  $pay_methods->payment_method_code;
     if($selected_otherpayment_code == "PSTD"){
      $order_payment_method_id = $pay_methods->payment_method_id;
      $payment_type="DP";
    }
   }
if($_REQUEST["ps_vendor_id"]=="10"){
          $sql_krcc="SELECT payment_method_id FROM mambophil_pshop_payment_method where payment_method_code='KIC'";
                          $database->setQuery($sql_krcc);
                          $database->loadObject($sql_krccs);
                          $order_payment_method_id=$sql_krccs->payment_method_id;
   }
	$expire_timestamp = 0;
  $expire_timestamp = date("Y-m-d H:i:s",mktime(0,0,0,$order_payment_expire_month, 1,$order_payment_expire_year));
  $expire_timestamp = "UNIX_TIMESTAMP('".$expire_timestamp."')";

	$placed_user_id = $_SESSION['workauth']["user_id"];

	$billing= mosgetparam( $_REQUEST, 'billing_address_info_id', 1);
  $bank_names_tc = isset($_REQUEST['bank_names_tc']) ? $_REQUEST['bank_names_tc'] : '';
  $cardTypeTc    = isset($_REQUEST['cardTypeTc']) ? $_REQUEST['cardTypeTc'] : 0;
	$qExt .= "INSERT INTO #__pshop_autoship_payment ";
            $qExt .= " (autoship_id, payment_method_id, order_payment_code, order_payment_number, order_payment_expire, ";
            $qExt	.= "  order_payment_name, ";
            $qExt	.= "  order_payment_status, creditcard_code, cdate, mdate,user_info_id,payment_type,accep_terms_cond,dt_terms_cond";
			if($financial_months > 0) {
				$qExt .= ",financial_months";
			}
			$qExt .= ",bank_name,card_type ) ";
            $qExt .= "VALUES (";
            $qExt .= $autoship_id . ",'"; // Working IBO
          	$qExt .= $order_payment_method_id . "','";
            $qExt .= $credit_card_code . "',";
            $qExt .= "ENCODE('". $order_payment_number . "','" . ENCODE_KEY . "') ,";
            $qExt .= $expire_timestamp . ",'";
            $qExt .= $order_payment_name . "','";
            $qExt .= $status . "', '";
            $qExt .= $creditcard_code . "',";
            $qExt .= "UNIX_TIMESTAMP()" . ",";
            $qExt .= "UNIX_TIMESTAMP()" . ", $billing, '$payment_type','".$accept_terms."',NOW()";

      if ($financial_months > 0) {
        if ($_REQUEST["ps_vendor_id"] == "1") {
          $financial_months = 1;
        }
        $qExt .= "," . $financial_months;
      }

			$qExt .= ",'$bank_names_tc',$cardTypeTc);"; 
	      $database->setQuery($qExt);
       	$database->query_batch();
       	unset($_SESSION['auto_credit_card']);
	}



function ShowConfiramtionmessages($ps_vendor_id,$Placed_orderstatus,$pibo){
  global $mosConfig_absolute_path,$mosConfig_live_site;

  if($ps_vendor_id == 10){
    showKoreaIBOFinalScreen();
  }else{
    $pibo=$_REQUEST["pibo"];
    if($pibo=="Y"){
      if($ps_vendor_id==1){
        modifyAutoshipRemoteArea($_REQUEST['order_id'],$_REQUEST['id']);
      }
      Miscountrymessages();
    }elseif($ps_vendor_id==1){
      modifyAutoshipRemoteArea($_REQUEST['order_id'],$_REQUEST['id']);
      if( $Placed_orderstatus=='5'){
        CC_rejected_msg();
      }else{
         Miscountrymessages();
    	 // showConfirmDataMex();
      }
    }elseif($ps_vendor_id==9){
    //ShowConfirmedData();
      if( $Placed_orderstatus=='5'){
        CC_rejected_msg();
      }else{
        //ShowConfirmedData();
        Miscountrymessages();
      }
    } elseif($ps_vendor_id==12){
    //ShowConfirmedData();
      if( $Placed_orderstatus=='X'){
        CC_rejected_msg();
      }else{
        //ShowConfirmedData();
        Miscountrymessages();
      }
    }else{
      Miscountrymessages();
    }
  }


// Unset Shop Fields
  require_once $GLOBALS['mosConfig_absolute_path'] . '/components/com_ibo/ibosetter.php';
  $setter = new ibosetter();
  $setter->unsetWorkingIBOShopFields();
}

/**
 * Function to update autoship remote area
 */
function modifyAutoshipRemoteArea($orderId,$userId){
	global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY;
  $sqlInfoOrder="SELECT
	o.order_id,
	o.user_id,
	o.order_type,
	substring_index(substring_index(o.ship_method_id, '|' ,- (-1)), '|' ,- (1)) carrier,
	substring_index(substring_index(o.ship_method_id, '|' ,- (-2)), '|' ,- (1)) name,
	substring_index(o.ship_method_id, '|' ,- (1)) ship_method_idx 
  FROM mambophil_pshop_orders o
  WHERE 1
  AND o.order_id IN($orderId)
  AND o.order_type IN(1) LIMIT 1";
  $database->setQuery($sqlInfoOrder);
  $database->loadObject($rstInfo);
  $ship_method_carrier = $rstInfo->carrier;
  $ship_method_name = $rstInfo->name;
  $ship_method_idx = $rstInfo->ship_method_idx;
  //check if ship_method_idx can be registered like autoship instead of regular service
  $getParamsShipping="SELECT params 
  FROM mambophil_pshop_shipping_rate 
  WHERE 1 
  AND shipping_rate_id IN($ship_method_idx) LIMIT 1";
  $database->setQuery($getParamsShipping);
  $database->loadObject($rstParams);
  $jsonParamsRA = json_decode($rstParams->params);
  // info autoship to update
  $getAutoShip="SELECT autoship_id FROM mambophil_pshop_autoship WHERE user_id = $userId LIMIT 1";
  $database->setQuery($getAutoShip);
  $database->loadObject($rstAuto);
  $autoshipId =$rstAuto->autoship_id;
  if(($jsonParamsRA->allow_remote_area_autoship=="true" || $ship_method_name=="Envio Incluido") && isset($autoshipId)){

    if($ship_method_name=="Envio Incluido"){
      $zip_b = $_REQUEST['zip']; // billing
      $zip_s = $_REQUEST['zipcodesmex']; // ship address
      $zipx  = $zip_b;
      if (isset($zip_s) && !empty($zip_s)) {
        $zipx = $zip_s;
      }

      $typeRedpackZone = getTypeZoneByZip($zipx,'redpack_remote_area');

      if($typeRedpackZone=='remote'){
        $ship_method_name='Zona Remota ECOEXPRESS 5Kg';
      }else{
        $ship_method_name='Envio ECOEXPRESS - Hasta 5 Kilos';
        //check if the local area has national coverage
        $rstCoverage = checkRedpackNationalCoverage($zipx);
        if ($rstCoverage === false) {
          #check if dhl hac coverage:
          $typeZoneDhl = getTypeZoneByZip($zipx,'dhl_remote_area');
          $ship_method_name='Servicio Regular Hasta 5 días hábiles';
          if($typeZoneDhl=='remote'){
            $ship_method_name='Servicio Regular Hasta 5 días hábiles (Zona Remota)';
          }
        }
      }
    }

    // calculate weight for autoship
    $sqlWeight="SELECT 
    SUM(( ai.product_quantity * p.product_weight )) /1000 autoshipWeight 
    FROM mambophil_pshop_autoship a 
    LEFT JOIN mambophil_pshop_autoship_item ai ON ai.autoship_id = a.autoship_id 
    LEFT JOIN mambophil_pshop_product p ON p.product_id = ai.product_id 
    WHERE 1 AND a.autoship_id IN ($autoshipId)
    GROUP BY a.autoship_id";
    $database->setQuery($sqlWeight);
    $database->loadObject($rstWeight);
    $autoshipWeight = $rstWeight->autoshipWeight;
    // get new autoship id and price
    $sqlNewAuto ="SELECT shipping_rate_id new_ship_id,
    ROUND((shipping_rate_value + (shipping_rate_value*0.16) ),2) new_ship_total 
    FROM mambophil_pshop_shipping_rate 
    WHERE 1 
    AND shipping_rate_zip_start = '0' 
    AND shipping_rate_country ='MEX;' 
    AND shipping_rate_name IN('$ship_method_name') 
    AND shipping_rate_weight_start <= '$autoshipWeight' 
    AND shipping_rate_weight_end >= '$autoshipWeight' LIMIT 1";
    $database->setQuery($sqlNewAuto);
    $database->loadObject($rstNewAuto);
    $new_ship_total = $rstNewAuto->new_ship_total;
    $new_ship_id = $rstNewAuto->new_ship_id;
    $newShipString = $ship_method_carrier."|".$ship_method_name."|".$new_ship_total."|".$new_ship_id;
    // update
    $sqlUpdateAutoship = "UPDATE mambophil_pshop_autoship 
    SET ship_method_id = '$newShipString' 
    WHERE autoship_id = ".$autoshipId;
    $database->setQuery($sqlUpdateAutoship);
    $database->query_batch();
  }
}

function getTypeZoneByZip($newZip,$belongsTo){
	global $database;
	$sqlZone="SELECT 
	CASE WHEN COUNT(postal_code) >= 1 THEN 'remote' ELSE 'local' END AS typeZone 
	FROM 
	mambophil_pshop_shipping_rate_remote_areas 
	WHERE 1 
	AND postal_code IN($newZip) 
	AND belongs_to IN('$belongsTo') 
	LIMIT 1";
	$database->setQuery($sqlZone);
	$database->loadObject($rstNewZone);
	$newTypeZone = $rstNewZone->typeZone;
	return $newTypeZone;
}

function checkRedpackNationalCoverage($newZip){
  $flagRemoteCoverge = true;
  //global $database;
  require_once($GLOBALS['mosConfig_absolute_path']."/components/com_redpack/config.php");
  require_once($GLOBALS['mosConfigPathNodeJs'] . '/NodeJs.php');
  $uniqueID = rand(11111, 99999);
  $nameFile = "lambda_redpack_ws".$uniqueID;
  $jsfile_content = 'const { LambdaClient, InvokeCommand } = require("@aws-sdk/client-lambda");
  const lambdaClient = new LambdaClient({
    region: "'.AWS_REGION.'",
    credentials :{
    accessKeyId: "'.ACCESS_KEY.'",
    secretAccessKey: "'.SECRET_KEY.'"
    },
  });
  const orderNumber=0
  const userId=0
  const statusCode="null"
  const validateWsZip="true"
  const zipCodeOrigin="03840"
  const zipCodeDestination="'.$newZip.'"
  const payload = {
    "body": `[{"orderNumber":"${orderNumber}","userId":"${userId}","statusCode":"${statusCode}","validateWsZip":"${validateWsZip}","zipCodeOrigin":"${zipCodeOrigin}","zipCodeDestination":"${zipCodeDestination}"}]`
  };
  const input = {
    FunctionName: "'.REDPACK_LAMBDA_FUNCTION.'", 
    InvocationType: "'.INVOCATION_TYPE.'",
    Payload: JSON.stringify(payload),
  };
  const command = new InvokeCommand(input);
  lambdaClient.send(command)
  .then((data) => {
    const decodedString = new TextDecoder().decode(data.Payload);
    const jsonObject = JSON.parse(decodedString);
    console.log(jsonObject.body);
  })
  .catch((error) => {
    console.error("Error when invoking Lambda function:", error);
    throw error;
  });';
  $init = array(
    "nameFile" => $nameFile,
  );
  $nodeFile = new NodeJs($init);
  $path_file = $GLOBALS['mosConfigPathNodeJs'] . '/';
  $nodeFile->createContentFileJs($path_file, $jsfile_content);
  // $nodeFile->getContentFile(true); # true:continue
  $nodeJsPath = $nodeFile->getFullPathFile();
  $output = null;
  $retval = null;
  $resultWsNationC = null;
  exec("node " . $nodeJsPath . ' 2>&1', $output, $retval);
  if (isset($output[0]) && !empty($output[0])) {
    $resultWsNationC = json_decode($output[0], true);
    unlink($nodeJsPath);
  }
  if(isset($resultWsNationC['requestStatus']) && $resultWsNationC['requestStatus']=='true'){
    $statusWs = $resultWsNationC['data'][0]['resultWS'][0]['status'];
   if($statusWs<>1){
    $flagRemoteCoverge = false;
   }
  }
  return $flagRemoteCoverge;
}



/**************************************************************************************************************
* Name   : ShowConfirmedData
* Author :
* Date   :
* Desc   : Showing the Confirmation Data
*
*
****************************************************************************************************************/

function ShowConfirmedData(){
	global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY;
	require_once( $mosConfig_absolute_path . '/administrator/components/com_phpshop/classes/ps_database.php' );
		?>
		<link href="<?php echo $mosConfig_live_site ;?>/components/com_iboregistration/demo.css" rel="stylesheet" type="text/css">
		<div id="div-regForm">
  <div class="form-title" align="center"></div>
  <div class="form-sub-title"></div>
  <?
    $new_username = $_REQUEST["username"];
    $sqlUser ="SELECT USR.id,AU.status_cd
                    FROM #__users USR
                    LEFT JOIN #__adv_users AU ON AU.id = USR.id
                    WHERE	USR.username='".$new_username."'";
                    //echo $sqlUser;
    $database->setQuery($sqlUser);
    $database ->loadObject($dbUser);
	  $new_userid = $dbUser->id;
	  //$linkAuto="index.php?option=com_phpshop&page=shop.browse&category_id=d1788f0f79d7586dbbc54486d60102da&category_parent_id=78afb96a047f67433453122393410306&option=com_phpshop&Itemid=770&workuserid=".$new_userid;
	  //$linkAuto='index.php?option=com_ibo&Itemid='.$_REQUEST["Itemid"].'&step_number=14&action_number=1&workuserid='.$new_userid;
	  //$linkAuto=$linkAuto ."&nextpage=autoshipmaint&direct_autoship=1";
	  //echo "<br><br><a href='";
    $linkAuto='index.php?option=com_ibo&Itemid='.$_REQUEST["Itemid"].'&step_number=14&action_number=1&workuserid='. $new_userid;
	  $linkAuto=$linkAuto ."&nextpage=shop_ibo_join";
    //echo ">123</a>";
		?>
		  <style type="text/css">

        /* message boxes: warning, error, confirmation */
        .notice {
           color: #000000;
           margin: 0.5em 0 0.5em 0;
           background-image: url(<?php echo $GLOBALS['mosConfig_image_site'];?>/themes/original/img/s_notice.png);
           background-repeat: no-repeat;
           background-position: 10px 50%;
           padding: 10px 10px 10px 36px;
        }

      </style>

            <script type="text/javascript">
              function closeMessage(){
                elm=document.getElementById("divAutoMessage");
                elm.style.display="none";
              }

              	function oculta(id){
      	 var elDiv = document.getElementById(id);
      	 elDiv.style.display='none';
      	}

      function muestra(id){
      	 var elDiv = document.getElementById(id);
      	 elDiv.style.display='';
      	}

      	function funcc(){
      	 oculta('plan_state');
      	 muestra('plan_state2');
        }
            </script>

<?php
  $linkAuto='index.php?option=com_ibo&Itemid='.$_REQUEST["Itemid"].'&step_number=14&action_number=1&workuserid='.$new_userid;
       //$linkAuto=$linkAuto ."&nextpage=autoshipnews";
       $linkAuto=$linkAuto ."&nextpage=autoshipnews&direct_autoship=1";
  $linkAuto2="index.php?option=com_ibo&Itemid=1159&step_number=14&nextpage=dashsum";
  $_SESSION['autoship_username']=$new_username;
  $_SESSION['autoship_password']="test";
?>

<div id='plan_state2' style='display:block;'>
  <table width="580" height="750" border="0" align="center" cellpadding="0" cellspacing="0" id="Tabla_01">
<tr>
		<td colspan="3">
			<img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/Enzacta_usa_PlaceAutoShip091012_01.jpg" width="580" height="26" alt="" /></td>
	</tr>
	<tr>
		<td rowspan="5">
			<img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/Enzacta_usa_PlaceAutoShip091012_02.jpg" width="25" height="724" alt="" /></td>
		<td background="images/Enzacta_usa_PlaceAutoShip091012_03.jpg" width="454" height="187">
			<div align="center" class="Enzacta_titulo">
          <p class="Enzacta_titulo"><?php echo _IBO_AUTOSHIP_TEXT01 . "<br>" . _IBO_AUTOSHIP_TEXT02;?></p>

          <p class="Enzacta_subtitulo"><?php $var=_IBO_AUTOSHIP_TEXT03;
                                          $var=explode("|",$var);
                                          echo $var[0] . $_SESSION['autoship_username'] . $var[1] .  $_REQUEST['password'] . $var[2] . $_REQUEST['name'] . $var[3]; //aguilas
                                    ?></p>
          <p class="Enzacta_subtitulo"><?php echo _IBO_AUTOSHIP_TEXT04;?></p>

          </div>
    </td>
		<td rowspan="5">
			<img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/Enzacta_usa_PlaceAutoShip091012_04.jpg" width="101" height="724" alt="" /></td>
	</tr>
	<tr>
		<td>
			<img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/Enzacta_usa_PlaceAutoShip091012_05.jpg" width="454" height="71" alt="" /></td>
	</tr>
	<tr>
		<td background="images/Enzacta_usa_PlaceAutoShip091012_06.jpg" width="454" height="295"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="5%">&nbsp;</td>
            <td width="89%"><ul>
              <li class="enzacta_contenido2">90% of the IBOs who subscribed to the AutoShip program make more in commissions per month.<br><br>
              </li>
              <li class="enzacta_contenido2">AutoShip allows you to qualify with 80 BV (Business Volume) any day of the month.<br><br>              </li>
              <li class="enzacta_contenido2">With AutoShip you receive your order automatically, without having to waste your time or effort.  It is hassle-free.<br><br>              </li>
              <li class="enzacta_contenido2">AutoShip keeps you active, entitling you to commissions.</li>
            </ul></td>
            <td width="6%">&nbsp;</td>
          </tr>
        </table>			</td>
	</tr>
	<tr>
		<td>
			<a href="<?php echo $linkAuto;?>"><img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/Enzacta_usa_PlaceAutoShip091012_07.jpg" alt="" width="454" height="109" border="0" /></a></td>
  </tr>
	<tr>
		<td>
			<a href="<?php echo $linkAuto2;?>"><img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/Enzacta_usa_PlaceAutoShip091012_08.jpg" alt="" width="454" height="62" border="0" /></a></td>
  </tr>
</table>
</div>
</div>
		<?php


}
function CC_rejected_msg(){
	global $PHPSHOP_LANG,$CURRENCY_DISPLAY,$mosConfig_live_site,$database;
	?>
		<link href="<?php echo $mosConfig_live_site ;?>/components/com_iboregistration/demo.css" rel="stylesheet" type="text/css">
		<div id="div-regForm">
  <div class="form-title" align="center"></div>
  <div class="form-sub-title"></div>
<div>
<table width="580" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td class="bg_PaymentRejected alcenter backwhite">
      <br /><br /><br /><br /><br /><br /><br /><br />
      <br /><br /><br /><br /><br /><br /><br /><br /><br />
      <?php echo _REGISTRATION_CC_DECLIENED_MSG; ?>
    </td>
  </tr>
  <tr>
     <td class="bg_FooterPaymentRejected"></td>
  </tr>
  <tr>
    <td align="center">
      <p class="Enzacta_subtitulo">
      <?php $var=_IBO_AUTOSHIP_TEXT03;
        $var=explode("|",$var);
        echo $var[0] . $_SESSION['autoship_username'] . $var[1] . $_REQUEST['password'] . $var[2] . $_REQUEST['name'] . $var[3]; //aguilas
      ?>
      </p>
    </td>
  </tr>
</table>
<?
if(isset($_SESSION["BanorteReference"]) || isset($_SESSION["OXXOReference"]) ){
require_once(CLASSPATH.'ps_html.php');
$ps_html= new ps_html;
}
if(isset($_SESSION["BanorteReference"])){

foreach($_SESSION["BanorteReference"] as $refrence) {
  $ps_html->display_Banorte_referencenumber($refrence);
}

 unset($_SESSION["BanorteReference"]);
}
if(isset($_SESSION["OXXOReference"])){

foreach($_SESSION["OXXOReference"] as $oxorefrence) {
  $ps_html->display_Oxoo_referencenumber($oxorefrence);
}
unset($_SESSION["OXXOReference"]);
}

?>
</div>
</div>

<?
}

function Miscountrymessages(){
	global $PHPSHOP_LANG,$CURRENCY_DISPLAY,$mosConfig_live_site,$database;
	?>
		<link href="<?php echo $mosConfig_live_site ;?>/components/com_iboregistration/demo.css" rel="stylesheet" type="text/css">
		<div id="div-regForm" >
  <div class="form-title" align="center"></div>
  <div class="form-sub-title"></div>
<div>
<table width="580" border="0" align="center" cellpadding="0" cellspacing="0" style="background-image:url(<?php echo $GLOBALS['mosConfig_image_site'] ;?>/images/stories/autoenvio/<?=_NEWREGISTRATION_CONFIRMATION_IMAGE_FILENAME ?>);">
  <tr>
    <td height="55">&nbsp;</td>
  </tr>
  <tr>
    <td align="center" class="Enzacta_subtitulo" height="55">

	 <?php  //echo _NEWREGISTRATION_CC_DECLIENED_MSG; ?>
	</td>
  </tr>
  <tr>
    <td align="center" height="200" valign="bottom">
   <br><br><br>
     <p class="Enzacta_subtitulo"><?php $var=_IBO_AUTOSHIP_TEXT03;
                                          $var=explode("|",$var);
                                          echo $var[0] . $_SESSION['autoship_username'] ."<br>". $var[1] . $_REQUEST['password'] ."<br>". $var[2] . $_REQUEST['name'] . $var[3]; //aguilas
                                    ?></p>

    </td>
  </tr>
</table>
<?
if(isset($_SESSION["BanorteReference"]) || isset($_SESSION["OXXOReference"]) || isset($_SESSION["payu"])){
require_once(CLASSPATH.'ps_html.php');
$ps_html= new ps_html;
}
if(isset($_SESSION["BanorteReference"])){

foreach($_SESSION["BanorteReference"] as $refrence) {
  $ps_html->display_Banorte_referencenumber($refrence);
}
 unset($_SESSION["BanorteReference"]);
}
if(isset($_SESSION["OXXOReference"])){

foreach($_SESSION["OXXOReference"] as $oxorefrence) {
  $ps_html->display_Oxoo_referencenumber($oxorefrence);
}
unset($_SESSION["OXXOReference"]);
}

//if payu is used as payment method
if(isset($_SESSION["payu"])){
  //get response of payu payment
  $sql = "SELECT order_payment_response FROM mambophil_pshop_order_payment where order_id=".$_SESSION['order_id'];
  $database->setQuery($sql);
  $payu_results = $database->loadObjectList();

  $database->setQuery( "SELECT * FROM #__users WHERE username='".$_SESSION['lastusername']."'");
  $database->loadObject( $user );
  require_once(CLASSPATH."ps_email.php");
  $ps_email = new ps_email;

  foreach($payu_results as $payu_result) {
    $payu_response = $payu_result->order_payment_response;
    parse_str (urldecode($payu_response),$pay_link);
    if($pay_link['URL_PAYMENT_RECEIPT_HTML']){
      $ps_html->display_payu_print_option($pay_link['URL_PAYMENT_RECEIPT_PDF']);
      if(!empty($user->email)){
        $payment_link = $pay_link['URL_PAYMENT_RECEIPT_HTML'];
        $subject = _PAYU_MAIL_SUBJECT;
        $param['username'] = $user->username;
        $content= '<a href="'.$payment_link.'" target="_blank">'._PAYMENT_RECEIPT_LINK.'</a>';
        $ps_email->SendGeneralMail($user->email, $subject, $content,'','', $param);
      }
    }
  }
  unset($_SESSION["payu"]);
}
if(isset($_SESSION['jpitem'])){
	unset($_SESSION['jpitem']);
}
if (isset($_SESSION['jpsignupitem'])) {
	unset($_SESSION['jpsignupitem']);
}
?>
</div>
</div>

<?
}

function showKoreaIBOFinalScreen(){
  global $PHPSHOP_LANG,$CURRENCY_DISPLAY,$mosConfig_live_site,$database, $mainframe;

?>

<link href="<?php echo $mosConfig_live_site ;?>/components/com_iboregistration/demo.css" rel="stylesheet" type="text/css">
<div id="div-regForm" >
  <div class="form-title" align="center"></div>
  <div class="form-sub-title"></div>
  <div>
    <table width="580" border="0" align="center" cellpadding="0" cellspacing="0" style="background-image:url(<?php echo $GLOBALS['mosConfig_image_site'] ;?>/images/stories/autoenvio/<?=_NEWREGISTRATION_CONFIRMATION_IMAGE_FILENAME ?>);">
      <tr>
        <td height="55">&nbsp;</td>
      </tr>
      <tr>
        <td align="center" class="Enzacta_subtitulo" height="55"></td>
      </tr>
      <tr>
        <td align="center" height="200" valign="bottom">
          <br><br><br>
          <p class="Enzacta_subtitulo">
            <?php
              $var=_IBO_AUTOSHIP_TEXT03;
              $var=explode("|",$var);
              echo $var[0] . $_SESSION['autoship_username'] ."<br>". $var[1] . $_REQUEST['password'] ."<br>". $var[2] . $_REQUEST['name'] . $var[3]; //aguilas
            ?>
          </p>
        </td>
      </tr>
    </table>

    <table border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
        <td align="left">
<?
  $order_id = $_REQUEST['order_id'];
  if(!empty($order_id)){
      $paymentQry = 'SELECT op.rejected_reason_cd, op.order_payment_response, pm.payment_method_code,o.order_status,
                            os.order_status_name,o.`vendor_id`
    							FROM #__pshop_order_payment op
    							INNER JOIN #__pshop_payment_method pm on op.payment_method_id = pm.payment_method_id
                  INNER JOIN mambophil_pshop_orders o ON o.`order_id` = op.`order_id`
                  LEFT JOIN mambophil_pshop_order_status os ON o.order_status = os.order_status_code
    							WHERE op.order_id = '.$order_id;
    	$database->setQuery($paymentQry);
    	$database->loadObject($paymentInfo);

    	$paymentMethodCode = $paymentInfo->payment_method_code;
      $order_status_code = $paymentInfo->order_status;
      $lastorder_status = convertTitle($paymentInfo->order_status_name);
      $vendor = $paymentInfo->vendor_id;

      $sql_vendor_info = "SELECT * FROM mambophil_pshop_vendor_order_contactinfo where vendor_id='".$vendor."'";
      $database->setQuery($sql_vendor_info);
      $database->loadObject($sql_vendor);
      $vendor_contactemail=$sql_vendor->contact_email;
      $vendor_contactphone=$sql_vendor->contact_phone;

      if($paymentMethodCode == "KIC"){
        if(!empty($paymentInfo->order_payment_response)){
          $chunks = explode(";",$paymentInfo->order_payment_response);
          $response_code = substr($chunks[6], (strpos($chunks[6],"=")+1));
        	$acquirer_nm = substr($chunks[14], (strpos($chunks[14],"=")+1));
          $acquirer_nm .= " : ".substr($chunks[15], (strpos($chunks[15],"=")+1));
        	$card_no = substr($chunks[25], (strpos($chunks[25],"=")+1));
          $res_msg = substr($chunks[21], (strpos($chunks[21],"=")+1));
        	$stat_msg = '';
        	if($order_status_code == '5'){
            $error_code = substr($chunks[6], (strpos($chunks[6],"=")+1));
            $error_text = substr($chunks[19], (strpos($chunks[19],"=")+1));
          }else{
            $authCode = substr($chunks[11], (strpos($chunks[11],"=")+1));
          }
        }
      }else{
      	parse_str($paymentInfo->order_payment_response,$response_values);
      	$order_no = $response_values['order_no'];
      	$amount = $response_values['amount'];
      	$acquirer_nm = $response_values['acquirer_nm'];
      	$card_no = $response_values['card_no'];
      	$authCode = $response_values['auth_no'];
      	$stat_msg = $response_values['stat_msg'];
      	if($order_status_code == '5'){
      		$error_code = $paymentInfo->rejected_reason_cd;
      		$res_msg = $response_values['res_msg'];

      		$errorDescriptionQry = "SELECT description FROM mambophil_pshop_order_payment_rejected_reasons
      								WHERE rejected_reason_cd = '$error_code';";
      		$database->setQuery($errorDescriptionQry);
      		$database->loadObject($errorDescription);

      		if($errorDescription)
      			$error_text = $errorDescription->description;
      	}
      }
?>

<style type="text/css">
/***	B A C K 	O F F I C E    M E S S A G E S		***/
.orderMsgBox { width:500px; color: #999; padding:10px;	font-family: "Lucida Grande", "Lucida Sans Unicode",  sans-serif; font-size:12px;}
.orderMsgBox a{ color:#f48138; text-decoration:none;}
.orderMsgBox a:hover{ color:#f48138; text-decoration:underline;}

.orderTitleBox{ background-color:#F6F6F6; padding: 12px; clear:both;}
.orderMsgBlue{
	background-image:url(<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/doveBlue-B.jpg); 
	background-position: left center;	
  margin-left:15px; margin-top:10px;  margin-bottom:10px; 
  padding-left: 40px;
  padding-top:8px;
  background-repeat:no-repeat;
  min-height: 35px;
}
.orderMsgBlue .msg{
  color:#1C75BC;
}
.orderMsgGreen{
	background-image:url(<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/doveGreen-B.jpg); 
	background-position: left center;
  margin-left:15px; margin-top:10px;   margin-bottom:10px;
  padding-left: 40px;
  padding-top:8px;
  background-repeat:no-repeat;
  min-height: 35px;
}
.orderMsgGreen .msg{
  color:#6BC127;
}
.orderMsgRed{
	background-image:url(<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/crossRed-B.jpg); 
	background-position: left center;	
  margin-left:15px; margin-top:10px;   margin-bottom:10px; 
  padding-left: 40px;
  padding-top:8px;
  background-repeat:no-repeat;
  min-height: 35px;
}
.orderMsgRed .msg{
  color:#BE1E2D;
}
.orderMsgOrange{
	background-image:url(<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/alertOrange-B.jpg); 
	background-position: left center;	
  margin-left:15px; margin-top:10px;   margin-bottom:10px; 
  padding-left: 40px;
  padding-top:8px;
  background-repeat:no-repeat;
  min-height: 35px;
}
.orderMsgOrange .msg{
 color:#F48138;
}
.bold{ font-weight:bold;}
</style>

<div class="orderMsgBox">

<div class="orderTitleBox">
  <?echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_PLACED_MESSASGE;?>. <span class="bold"><?echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_ORDER_REVIEW_STATUS_MESSAGE;?></span>
</div>

<div class="orderMsgBlue">
  <span class="msg bold"><? echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_ORDER_REVIEW_NUMBER_MESSAGE;?></span> <span class="bold">
    <?php
		if($mainframe->getUser()->gid == 3){ ?>
			<a href="<?= $mosConfig_live_site."/index.php?option=com_ibo&Itemid=364&step_number=15&nextpage=ordermaintdetail&order_id=".$order_id ?>" >
				<?= $order_id; ?>
			</a>
		<?php } else
			echo $order_id;
		?>
  </span>
</div>

<? if($order_status_code=="J"){?>
<div class="orderTitleBox">
 <?echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_STATUS_MESSAGE;?> <span class="bold"><? echo $lastorder_status; ?></span>
</div>
<div class="orderMsgGreen">
  <span class="msg bold"><?echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_PLACED_MESSASGE_CONFIRM;?></span>
  <br /><span><? echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_ORDER_HISTORY_MESSASGE;?></span>
</div>
<? }elseif($order_status_code=="5"){?>
<div class="orderTitleBox">
 <? echo _VOLUME_STATUS; ?>: <span class="bold"><? echo $lastorder_status; ?></span>
</div>

<div class="orderMsgRed">
  <span class="msg bold"><?echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_ORDER_PAYMENT_NOT_MESSASGE;?></span>
  <br /><span><?echo $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_CONFRIMATION_ORDER_CUSTOMER_CONTACT_MESSASGE;?><br />
    <a href="#"><? echo $vendor_contactemail?></a> or <? echo $vendor_contactphone?></span>
</div>
 <? }else{ ?>
<div class="orderTitleBox">
 <? echo _VOLUME_STATUS; ?>: <span class="bold"><? echo $lastorder_status; ?></span>
</div>
<?
    }

    if($paymentMethodCode == "KRCC" || $paymentMethodCode == "KIC") {
    	echo '<div class="orderTitleBox">';
    	echo $error_code == '' ? '' : '<span>'.$PHPSHOP_LANG->_PHPSHOP_ERROR.': '.$error_code.' '.$error_text.'</span><br/>';
    	echo ($stat_msg == '' || $stat_msg == '') ? '' : '<span>'.$PHPSHOP_LANG->_PHPSHOP_ERROR.': '.$stat_msg.'</span><br/>';
    	echo $res_msg == '' ? '' : '<span>'.$res_msg.'</span><br/>';
    	echo $order_no == '' ? '' : '<span>'._AUTOSHIP_ORDER_ID.': '.$order_no.'</span><br/>';
    	echo $amount == '' ? '' : '<span>'._AUTOSHIP_ORDER_AMOUNT.': '.number_format($amount).'</span><br/>';
    	echo $acquirer_nm == '' ? '' : '<span>'.$PHPSHOP_LANG->_PSHOP_ISSUER_NAME.': '.$acquirer_nm.'</span><br/>';
    	echo ($card_no == '' || $card_no == '****')  ? '' : '<span>'.$PHPSHOP_LANG->_PSHOP_CARD_NUMBER.': '.$card_no.'</span><br/>';
    	echo $authCode == '' ? '' : '<span><strong>'._CONFIRMATION_NUMBER.': '.$authCode.'</strong></span><br/>';
    	echo '</div>';
    }
  }
?>
</div>
        </td>
      </tr>
    </table>
  </div>
</div>
<?
}

function showConfirmDataMex(){
	global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY;
	require_once( $mosConfig_absolute_path . '/administrator/components/com_phpshop/classes/ps_database.php' );
		?>
		<link href="<?php echo $mosConfig_live_site ;?>/components/com_iboregistration/demo.css" rel="stylesheet" type="text/css">
		<div id="div-regForm">
  <div class="form-title" align="center"></div>
  <div class="form-sub-title"></div>
  <?
    $new_username = $_REQUEST["username"];
    $sqlUser ="SELECT USR.id,AU.status_cd
                    FROM #__users USR
                    LEFT JOIN #__adv_users AU ON AU.id = USR.id
                    WHERE	USR.username='".$new_username."'";
    $database->setQuery($sqlUser);
    $database ->loadObject($dbUser);
	  $new_userid = $dbUser->id;

	  $linkAuto='index.php?option=com_ibo&Itemid='.$_REQUEST["Itemid"].'&step_number=14&action_number=1&workuserid='.$new_userid;
	  $linkAuto=$linkAuto ."&nextpage=autoshipmaint&direct_autoship=1";

		?>
		  <style type="text/css">

        /* message boxes: warning, error, confirmation */
        .notice {
           color: #000000;
           margin: 0.5em 0 0.5em 0;
           background-image: url(<?php echo $GLOBALS['mosConfig_image_site'];?>/themes/original/img/s_notice.png);
           background-repeat: no-repeat;
           background-position: 10px 50%;
           padding: 10px 10px 10px 36px;
        }

      </style>

      <script type="text/javascript">
        function closeMessage(){
          elm=document.getElementById("divAutoMessage");
          elm.style.display="none";
        }
      </script>

     <div class="notice" style="display:block;" id="divAutoMessage" name="divAutoMessage">
<!--<link href="templates/JavaBeanIBOMexico/css/template_css.css" rel="stylesheet" type="text/css"/>-->

<table width="580" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td width="580" colspan="3"><table id="Tabla_01" width="580" height="602" border="0" cellpadding="0" cellspacing="0">
      <tr>

        <td rowspan="4"><img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/stories/autoenvio/Enzacta_mx_nuevoIbo_01.jpg" width="20" height="551" alt="Nuevo IBO Enzacta" /></td>
        <td colspan="3"><img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/stories/autoenvio/Enzacta_mx_nuevoIbo_02.jpg" width="560" height="22" alt="Nuevo IBO Enzacta" /></td>
      </tr>
      <tr>
        <td colspan="2" background="images/stories/autoenvio/Enzacta_mx_nuevoIbo_03.jpg" width="458" height="184"><div align="center" class="Enzacta_titulo">
          <p class="Enzacta_titulo"><?php echo _IBO_AUTOSHIP_TEXT01 . "<br>" . _IBO_AUTOSHIP_TEXT02;?></p>

          <p class="Enzacta_subtitulo"><?php $var=_IBO_AUTOSHIP_TEXT03;
                                          $var=explode("|",$var);
                                          echo $var[0] . $_REQUEST['username'] . $var[1] . $_REQUEST['password'] . $var[2] . $_REQUEST['name'] . $var[3]; //aguilas
                                    ?></p>
          <p class="Enzacta_subtitulo"><?php echo _IBO_AUTOSHIP_TEXT04;?></p>

          </div></td>
        <td rowspan="2"><img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/stories/autoenvio/Enzacta_mx_nuevoIbo_04.jpg" width="102" height="264" alt="Nuevo IBO Enzacta" /></td>
      </tr>
      <tr>
        <td colspan="2"><img src="<?php echo $GLOBALS["mosConfig_image_site"]; ?>/images/stories/autoenvio/Enzacta_mx_nuevoIbo_05.jpg" width="458" height="80" alt="Nuevo IBO Enzacta" /></td>
      </tr>
      <tr>
        <td background="images/stories/autoenvio/Enzacta_mx_nuevoIbo_06.jpg" width="290" height="265"><p class="Enzacta_acotacion">Sab&iacute;as que ...</p>

          <p align="justify" class="Estilo55"><?php echo _IBO_AUTOSHIP_TEXT05;?></p></td>
        <td colspan="2" background="images/stories/autoenvio/Enzacta_mx_nuevoIbo_07.jpg" width="270" height="265"><table width="100%" border="0" cellspacing="0" cellpadding="0">

          <tr>
            <td width="23%">&nbsp;</td>
            <td width="57%"><div align="center" class="puntos">
              <p class="Estilo56"><strong><?php echo _IBO_AUTOSHIP_TEXT06;?></strong></p>
              <p class="Estilo26"><?php echo _IBO_AUTOSHIP_TEXT07;?></p>
            </div></td>
            <td width="20%">&nbsp;</td>
          </tr>


        </table></td>
      </tr>
      <tr>
        <td colspan="4" width="580" height="50"><table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td width="3%">&nbsp;</td>
            <td width="43%"><div align="center"><a href="<?php echo $linkAuto. "&btnYes=". _IBO_AUTOSHIP_YES;?>" class="Enzacta_Autoenvio"><?php echo _IBO_AUTOSHIP_TEXT08;?></a></div></td>
            <td width="8%">&nbsp;</td>

            <td width="43%"><div align="center"><a href="<?php echo $linkAuto. "&btnNo=". _IBO_AUTOSHIP_NO;?>" onclick="javacript:closeMessage();" class="Enzacta_Autoenvio"><?php echo _IBO_AUTOSHIP_TEXT09;?></a></div></td>
            <td width="3%">&nbsp;</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td width="20" height="1"></td>
        <td width="290" height="1"></td>

        <td width="168" height="1"></td>
        <td width="102" height="1"></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
</table>

</div>
</div>


		<?php
}
function showIBOplacedmessage($Last_Username){
  global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY;

  ?>
		<link href="<?php echo $mosConfig_live_site ;?>/components/com_iboregistration/demo.css" rel="stylesheet" type="text/css">
		<div id="div-regForm">
  <div class="form-title" align="center"></div>
  <div class="form-sub-title"></div>
<div>
<table width="580" border="0" align="center" cellpadding="0" cellspacing="0">
  <tr>
    <td height="55">&nbsp;</td>
  </tr>
  <tr>
    <td align="center" class="Enzacta_subtitulo" height="55">
	 <?php  echo _NEWREGISTRATION_DBERROR_DECLIENED_MSG; ?>
	</td>
  </tr>
  <tr>
    <td align="center" height="200">
     <p class="Enzacta_subtitulo"><?php $var=_IBO_AUTOSHIP_TEXT03;
                                          $var=explode("|",$var);
                                          echo $var[0] . $Last_Username; //aguilas
                                    ?></p>

    </td>
  </tr>
</table>
</div>
</div>

<?
}
function sendadminmailtreeissues($treeplacement,$username) {
  global $mosConfig_live_site, $mosConfig_sitename,$mosConfig_mailfrom, $mosConfig_fromname;
  $title="Duplicate Tree issues - ".$mosConfig_live_site;
  $subject = "Possible Duplicate Tree issue $username";
  $sub_title = "";
  $msg="<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"5\">
            <tr>
                <td><strong>Location:</strong></td>
                <td class=\"Estilo55\"><strong>$mosConfig_live_site</strong></td>
              </tr>
              <tr>
                <td><strong>TREE POSITION:</strong></td>
                <td class=\"Estilo55\"><strong>$treeplacement</strong></td>
              </tr>
              <tr>
                <td><strong>NEW IBO NO:</strong></td>
                <td class=\"Estilo55\"><strong>$username</strong></td>
              </tr>
            </table>";
  $email = "aravindpilla@gmail.com";
  //Sending Email Information
  require_once(CLASSPATH."ps_email.php");
  $ps_email = new ps_email;
  $ps_email->SendSystemInfo($email, $subject, $msg, $title, $sub_title, $_REQUEST);
}
function community_product($productid){
    global $database;
    $community = '';
    if($productid > 0) {
     $sql = "select community_id from #__plantypes  where plan_product_id=".$productid;
     $database->setQuery($sql);
     $database->loadObject($community);
    }
    if(empty($community)){
    return 0;
    }
    return $community->community_id;
}


   /**************************************************************************************************************
* Name   : enterIbo
* Author :
* Date   :
* Desc   : step 2 is to navigate the tree
*
*
****************************************************************************************************************/
function showKoreanCountrySpecificData(&$d){
	global $database,$mosConfig_absolute_path,$mainframe,$mosConfig_live_site,$mosConfig_secure_live_site;
  /* Load the phpshop main parse code */
  require_once( $mosConfig_absolute_path.'/components/com_phpshop/phpshop_parser.php' );
  $PHPSHOP_LANG =& new phpShopLanguage();

	require_once( $mosConfig_absolute_path . '/administrator/components/com_phpshop/classes/ps_html.php' );
	require_once( $mosConfig_absolute_path . '/administrator/components/com_phpshop/classes/ps_database.php' );
	$ps_html = new ps_html;

  // show the images at top of screen
  //echo HTML_ibo_maint::step_registration(_STEP_COUNTRY_DATA);
//	$countryJoinCode = mosgetparam( $d, 'cntry_join_cd', '');
	      $vendor_id = mosGetParam( $_REQUEST, 'country', '');
        if($_REQUEST['editmode']){
        $vendor_id = mosGetParam( $_REQUEST, 'editcountry', '');
        }
        if(!$vendor_id){
         $vendor_id =  $_SESSION["ps_vendor_id"];  // for public registration
         }
  // gets the country info based on the plan type selected.
	//$sql = "SELECT * FROM #__pshop_country WHERE `country_id` ='".$countryJoinCode."' AND `activity_country_ind` = 'Y'";
  $sql = "SELECT country_name FROM `#__pshop_vendor` v INNER JOIN #__pshop_country c ON v.vendor_country = c.country_3_code WHERE  v.vendor_id = ".$vendor_id." AND  `activity_country_ind` = 'Y'";
  //echo "This is the sql for country ".$sql;
  $database->setQuery($sql);
	$database -> loadObject($country_lookup);
  if ($database -> getErrorNum()) {
  	echo $database -> stderr();
  	return false;
  }

  $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
  $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")).$s;
  $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
  $self_URL = $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];
  $self_URL_array = explode("?",$self_URL);
  if($self_URL_array) {
  $self_URL =  $self_URL_array[0];
  }
  $self_URL_OK = $self_URL."?option=com_ibo&Itemid=".$_REQUEST["Itemid"]."&step_number=6001&action_number="._STEP_VALIDATE_KOREAN_COUNTRY_DATA."&country = ".$vendor_id;
  ?>

  <form action="<?php echo sefRelToAbs('index.php'); ?>" method="post">
    <input type="hidden" name="option" value="com_ibo" />
    <input type="hidden" name="Itemid" value="<?php echo $_REQUEST["Itemid"];?>" />
  	<input type="hidden" name="step_number" value="6001" />
  	<input type="hidden" name="action_number" id = "Korean_process_num" value="<?php echo ($vendor_id == 10)? _STEP_TEMPPOST_KOREAN_COUNTRY_DATA:1; ?>">
  	<input type="hidden" name="ok_url" value="<?php echo $self_URL_OK;?>">

  	<!----- Registration Form ---->
  	<table border="0" cellpadding="3" cellspacing="0" width="100%">

  	<tr>
  		<?php
  		  // Get country info based on join country code.
        $row = $ps_html->get_country($country_lookup->country_id);

        $d["country_id"] = mosgetparam( $d, 'country_id', $country_lookup->country_id);
        $d["cntry_join_cd"] = mosgetparam( $d, 'cntry_join_cd', $country_lookup->country_id);
        $d["country"] = mosgetparam( $d, 'country', $country_lookup->country_3_code);
        $d["country_desc"] = mosgetparam( $d, 'country_desc', $country_lookup->country_name);
        $d["CUSTOMER_TYPE"] = mosgetparam( $d, 'CUSTOMER_TYPE', "I");
        $request = $d;
        require_once($mosConfig_absolute_path."/components/com_advanced_registration/korean_name_check_template.html.php");
  		?>

  	</table>
    <span style="padding-left:400px;color:Red" id="Korenssneeror"></span>
    <br>
    <table width="100%">
      <tr>
     		<td colspan="3" align="center">
  	   <!--  <input type="submit" name="mysubmit" value="<?php echo $PHPSHOP_LANG->_PHPSHOP_COUPON_SUBMIT_BUTTON; ?>" class="btnStandard"/><br><br><br>	  -->
  	    </td>
  	  </tr>
  	</table>

	</form>
<?php
}

/**************************************************************************************************************
* Name   : Show Country Specific Questions
* Author :
* Date   :
* Desc   : Things like Korea specific items go here..
*
****************************************************************************************************************/
function tempPostKoreanCountrySpecificData(){
  $type = ($_REQUEST['enrolType']=="Individual") ? "ind" : "bus";
  $ssn_name = $_REQUEST['name'] = $_SESSION["name"] = $_REQUEST[$type.'kssn_name'];
  $ssn_number1 = $_REQUEST["jumin1"] = $_REQUEST[$type."jum1"];
  $ssn_number2 =$_REQUEST["jumin2"] = $_REQUEST[$type."jum2"];

  $ssn_name = trim($ssn_name);
  $ssn_key_check = ($ssn_name.$ssn_number1.$ssn_number2);
  $ssn_key = isset($_REQUEST['ssn_key']) ? $_REQUEST['ssn_key'] : $_SESSION['form']->ssn_key;

  $_SESSION['KOREAN_REG_VALUES'] = serialize($_REQUEST) ;

  //echo "CHECK: SSNKEY:$ssn_key NAME:$ssn_name SSN1:$ssn_number1 SSN2:$ssn_number2 ";

    //comment this line
    //$_REQUEST["skipssn"]=1;

  if(($ssn_key == $ssn_key_check) || $_REQUEST["skipssn"]==1){
    // SSN Already Validated, skip the lookup
    $_REQUEST['result'] = 1;
    validateKoreanCountrySpecificData();
    return true;
  }



  ini_set('default_charset','UTF-8');
?>
  <form action="https://www.enzacta.com/Customer/components/com_ibo/korean/ssnnamevalidation.php" method="post" id="adminForm2" name="adminFormsearch">
     <?php
      $En_Values  =  $_REQUEST;
      foreach($En_Values as $key=>$value){
        if($En_Values['enrolType'] == 'Individual'){
          if(strpos($key, 'ind') !== FALSE && strpos($key, 'ind') == 0 ){
            $key_new =  substr($key, 3);
            $En_Values[$key_new] = $En_Values[$key];
          }
        }else{
          if(strpos($key, 'bus') !== FALSE && strpos($key, 'bus') == 0 ){
            //echo "<br>KEY: $key VALUE: $value";
            $key_new =  substr($key, 3);
            $En_Values[$key_new] = $En_Values[$key];
          }
        }
      }
      $_REQUEST = $En_Values ;
      //settting the serialzied values into request
      foreach($_REQUEST as $key => $value){
        if($key == "joinproductradio"){
        continue;
        }
        /*if ($key == "name")  {
          $_SESSION["name"]=$value;
        } */
        if ($key == "kssn_name")  {
          $_SESSION["name"]=$value;
          echo ' <input type="hidden" name="name" value="'.$value.'"> ';
        }
        if ($key == "jum1" )  {
          $_REQUEST["jumin1"] = $_REQUEST["jum1"];
          echo ' <input type="hidden" name="jumin1" value="'.$_REQUEST["jumin1"].'"> ';
        }

        if ($key == "jum2")  {
          $_REQUEST["jumin2"] = $_REQUEST["jum2"];
          echo ' <input type="hidden" name="jumin2" value="'.$_REQUEST["jumin2"].'"> ';
        }
       ?>
        <input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>">
       <?php
      }
     ?>
     <input type="submit" name="mysub" id="mysub" value="<?php echo mb_convert_encoding("다음화면으로", "utf-8", "utf-8");?>">
  </form>
  <script>
   document.getElementById("adminForm2").submit();
  </script>

<?php
}


/**************************************************************************************************************
* Name   : Show Country Specific Questions
* Author :
* Date   :
* Desc   : Things like Korea specific items go here..
*
****************************************************************************************************************/
function validateKoreanCountrySpecificData(){
    // Korean service doesn't provide return of description. Have to
    // manually decode from return status code

    $_REQUEST["tax_id"] = mosgetparam( $_REQUEST, 'jumin1', '').mosgetparam( $_REQUEST, 'jumin2', '');
    $_REQUEST["last_name"] = mosgetparam( $_SESSION, 'name', '');

    unset($_REQUEST["name"]);
    // store all request back to session.
    request_form_back_to_session($_REQUEST);

    $result = mosgetparam( $_REQUEST, 'result', '');
    $errorMessage = '';

    //force in good response, since the thing never seem to work.
    // Be sure to remove later.
    //$result = 1;
    if (empty($result)) {
      $errorMessage .= "알 수없는 오류가 다시 시도해 보시기 바랍니다.";
    } elseif ($result == '1' ) {
      $ssn_name = trim($_REQUEST['last_name']);
      $ssn_number1 = $_REQUEST["jumin1"];
      $ssn_number2 =$_REQUEST["jumin2"];
      $ssn_key = $_SESSION['form']->ssn_key = $_REQUEST['ssn_key'] = md5($ssn_name.$ssn_number1.$ssn_number2);
      //echo "<br>SET: SSNKEY:$ssn_key NAME:$ssn_name SSN1:$ssn_number1 SSN2:$ssn_number2 ";
      // all good
    } elseif ($result == '2') {
      $errorMessage = "주민번호와 성명 불일치";
    } elseif ($result == '3') {
      $errorMessage = "DB미등록";
    } elseif ($result == '4') {
      $errorMessage = "주민번호 로직 오류";
    } elseif ($result == '5') {
      $errorMessage = "SYSTEM 장애";
    } elseif ($result == '9900') {
      $errorMessage = "유효한 정보가 안입니다";
    } else {
        $errorMessage .= "알 수없는 오류가 다시 시도해 보시기 바랍니다.";
    }


   /* if ($result == '1') {
       //echo "HIT 1 ";
       //ibo_maint(_STEP_PLACEMENT);
       showMainModule($result) ;
    } else {
       //echo "Error hit, display errror and show screen again.";
       displayErrorMessage($errorMessage);
       showKoreanCountrySpecificData($_REQUEST);
  	}*/
   showConfirmData($result,$errorMessage);
}

function displayErrorMessage($errorMessage) {
    $errorMessage = "<tr><td spancols=3 class='errorwarning'>Correct the following Errors:</td></tr><tr><td spancols=3 class='errorwarning'>".$errorMessage."</td></tr>";
    ?>
    <link rel="stylesheet" href="<?php echo "$mosConfig_live_site/components/$option/style.css";?>" type="text/css" />
    <table>
      <?php echo $errorMessage;?>
    </table>
    <br><br>
    <?php
}
function request_form_back_to_session($request,$debug='N'){
  if ($debug == 'Y') {
      echo "<BR> Start of copy Request Back to Session";
  }
	foreach($request as $key => $value){

 	  if ($key!='phpshop' && $key!='sessioncookie'&& $key!='option'&& $key!='Itemid' && $key!='action_number' && $key!='next_action_number' && $key!='$forceshow'
     && substr($key,0,1)!="1"
     && substr($key,0,1)!="2"
     && substr($key,0,1)!="3"
     && substr($key,0,1)!="4"
     && substr($key,0,1)!="5"
     && substr($key,0,1)!="6"
     && substr($key,0,1)!="7"
     && substr($key,0,1)!="8"
     && substr($key,0,1)!="9"
     && substr($key,0,1)!="0"
     ){
   	    //$copyString = "\$_SESSION['form']->".$key."=\$request['".$key."'];";
   	    $copyString = "\$_SESSION[\"form\"]->".$key."=\$request[\"".$key."\"];";
   	    if ($debug == 'Y') {
	         echo "<BR> going to be the copy ".$copyString;
	      }
	      //echo "<BR>2 SESSION--".$key."-- ".$_SESSION["form"]->$key;
	      //exit;
	      eval($copyString);
        //echo "<BR>2 SESSION ".$copyString;
    }
	}
	if ($debug == 'Y') {
      echo "<BR> End of copy";
  }//exit;
}

// Korean Bank Validation


function ValidateBankAccount($strResId,$strBankCode,$strAccountNo,$koreanName, $strGbn = '1'){
    require_once( $GLOBALS['mosConfig_absolute_path'] . '/components/com_ibo/korean/korea_service.cfg.php' );

    $bank_key_check = ($strBankCode.$strAccountNo.$koreanName);
    $bank_key = isset($_REQUEST['bank_key']) ? $_REQUEST['bank_key'] : $_SESSION['form']->bank_key;
    //echo "<br> CHECK: BankKey:$bank_key BankCode:$strBankCode BankAcc:$strAccountNo Name:$koreanName ";
    if($bank_key == $bank_key_check){
      return true;
    }else{
      $random = time();
      $post_data['service'] = '1';
      $post_data['svcGbn'] = '5';
      $post_data['strGbn'] = $strGbn; //1 for Persona, 2 for business
      $post_data['niceUid'] = NICE_UID; // 'Nenzacta';    // OLD: Nenzacta1
      $post_data['svcPwd'] = SVC_PWD; //'1234';   // OLD: 4CkRg3FN
      $post_data['inq_rsn'] = '10';
      $post_data['strNm'] = $koreanName;
      $post_data['strOrderNo'] = $random;
      $post_data['strResId'] = $strResId;
      $post_data['strBankCode'] = $strBankCode;
      $post_data['strAccountNo'] = $strAccountNo;

      $post_url = SVC_URL; // "https://secure.nuguya.com/nuguya2/service/realname/sprealnameactconfirm.do";

      $poststring = '';
      foreach($post_data AS $key => $val){
        $poststring .= ($key) . "=" . urlencode($val) . "&";
      }
      $poststring = substr($poststring, 0, -1);

      $post_data = $post_url.'?'.$poststring;

      /*
      //echo $post_data;
      $ch = curl_init();
      $timeout = 5; // set to zero for no timeout
      curl_setopt ($ch, CURLOPT_URL, $post_data);
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      //curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application / x-www-form-urlencoded; charset=utf-8", "Content-length: ".strlen($poststring)));
      curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
      $response = curl_exec($ch);
      curl_close($ch);
      */

      require_once($GLOBALS['mosConfigPathNodeJs'] . '/NodeJs.php');
	  $uniqueID = rand(11111, 99999);
      $nameFile = 'newpathkorearegister_'.$uniqueID;

      $init = array(
          "method" => "GET",
          "url" => $post_data,
          "nameFile" => $nameFile,
      );

      $nodeFile = new NodeJs($init);
      $nodeFile->buildFileServerJs();
      $nodeJsPath = $nodeFile->getFullPathFile();

      $output = null;
      $retval = null;
      $response= null;

      exec("node " . $nodeJsPath . ' 2>&1', $output, $retval);

      if (isset($output[0]) && !empty($output[0])) {
          $response = $output[0];
      }

      $chunks = explode("|", $response);

      if(count($chunks)>1){
        /*
          IMPORTANT: Please refer Mantis 2586 for the information about this service
           0000 => Success
           E999 => internal communications error
           We dont block the system on communication error, so error E999 is pass by
        */
        //var_dump($chunks);
        if($chunks[1] == '0000'){ // $chunks[1] == 'E999' ||
          $bank_key = $_REQUEST['bank_key'] = $_SESSION['form']->bank_key = ($strBankCode.$strAccountNo.$koreanName);
          //echo "<br> BankKey:$bank_key BankCode:$strBankCode BankAcc:$strAccountNo Name:$koreanName ";
          return true;
        }else{
          $_REQUEST['bank_error_code'] = $chunks[1];
          $_REQUEST['bank_error_msg'] = $chunks[2].'('.$chunks[1].')';
          return false;
        }
      }else{
        return false;
      }
    }
  }

function ValidateKoreaNameBirth($szName2, $szBirth2, $szSex2){
    $is_valid = false;
    $message = '';

    $errors = array(  1=> '정상',
                      2=> '미존재',
                      5=> '시스템장애(체크사항: 소켓서버 아이피 및 포트 확인)',
                      6=> '파라메터 에러(체크사항: 통신호출 파라메터 확인)'
                    );

    require_once( $GLOBALS['mosConfig_absolute_path'] . '/components/com_ibo/korean/korea_name_check.cfg.php' );
    $szJobCode	= mb_convert_encoding('100', "EUC-KR", "utf-8");	// 업무구분코드
    $szSex			= mb_convert_encoding($szSex2, "EUC-KR", "utf-8");		// 성별
    $szBirth		= mb_convert_encoding($szBirth2, "EUC-KR", "utf-8");		// 생년월일
    $szName			= mb_convert_encoding($szName2, "EUC-KR", "utf-8"); //$_POST["name"];		// 이름
    $szHp			  = mb_convert_encoding('', "EUC-KR", "utf-8");			// 휴대폰번호
    $szEmail		= mb_convert_encoding('', "EUC-KR", "utf-8");		// 이메일
    $szReqNum		= date('YmdHis').rand(100000, 999999); // Current time + Rand number //현재 시각 구하기
    $szReqNum		= mb_convert_encoding($szReqNum, "EUC-KR", "utf-8");

    $szDocCode	= mb_convert_encoding(szDocCode, "EUC-KR", "utf-8");				// 전문종별코드
    $szHostIP	  = szHostIP;		// 서울신용평가정보 IP 210.207.91.239
    $szPort		  = mb_convert_encoding(szPort, "EUC-KR", "utf-8");				// 소켓 Port 설정(서울신용평가정보에서 제공)
    $szSvcNo	  = mb_convert_encoding(szSvcNo, "EUC-KR", "utf-8");				// 서비스번호
    $szUserId 	= mb_convert_encoding(szUserId, "EUC-KR", "utf-8");			// 회원사ID 설정(서울신용평가정보에서 제공)
    $szReturnCode = "000";				// 응답코드
    $szSendBuf	= "";					// 요청전문 Buf
    $szRecvBuf	= "";					// 결과전문 Buf
    $szResult	  = "";					// 식별확인결과값
    $szSKey		  = "";					// SCI식별값

    $timeout = 3;                    // receive timeout 설정 (단위:초)

  	//회원사 아이디 8 byte padding
  	$szUserId = str_pad($szUserId, 8, ' ');
  	//성명 20 byte padding
  	$reqName = $szName;
  	$szName = str_pad($reqName, 20, ' ');
  	//요청번호 40 byte padding
  	$reqNum = $szReqNum;
  	$szReqNum = str_pad($reqNum, 40, ' ');

    /******************************************************************************************
	   * 메시지 전송
	   *****************************************************************************************/
  	if(!strcmp($szJobCode, "100")) {
  		$DocLen  = 53;
  		$szSendBuf = $szUserId.$szDocCode.$szJobCode.$szReturnCode.$szSvcNo.$szReqNum.$szBirth.$szSex.$szName;
  	}else if(!strcmp($szJobCode, "110")) {
  		$DocLen  = 64;
  		$reqHp = $szHp;
  		$szHp = str_pad($reqHp, 11, ' ');
  		$szSendBuf = $szUserId.$szDocCode.$szJobCode.$szReturnCode.$szSvcNo.$szReqNum.$szBirth.$szSex.$szName.$szHp;
  	}else if(!strcmp($szJobCode, "120")) {
  		$DocLen  = 93;
  		$reqEmail = $szEmail;
  		$szEmail = str_pad($reqEmail, 40, ' ');
  		$szSendBuf = $szUserId.$szDocCode.$szJobCode.$szReturnCode.$szSvcNo.$szReqNum.$szBirth.$szSex.$szName.$szEmail;
  	}else{
  		$szResult = "6"; //전문 항목 오류
  	}

    try{
  	  //COM 호출
      $socket = new COM("SciClient.SCICli");
      $szRecvBuf = $socket->SciClient($szHostIP, $szPort, $timeout, $szSendBuf);

      /******************************************************************************************
  	   * 식별확인 결과값 처리
       ******************************************************************************************/
    	$szDocCode = substr($szRecvBuf, 8, 4);
    	$szJobCode = substr($szRecvBuf, 12, 3);

      if(strlen($szRecvBuf) < $DocLen) {
      		// 전문 응답길이 오류
      		$szResult = "6";
    	} else {

    		/* 전문 응답코드 확인 */
    		if(!strcmp($szDocCode, "0210")) {

    			$szReturnCode = substr($szRecvBuf, 17, 3);

    			if(!strcmp($szReturnCode, "000")) {

    				if(!strcmp($szJobCode, "100")) {
    					$szResult   = substr($szRecvBuf, 93, 1);
    				}else if(!strcmp($szJobCode, "110")) {
    					$szResult   = substr($szRecvBuf, 104, 1);
    					$szSKey = substr($szRecvBuf, 105, 64);
    				}else if(!strcmp($szJobCode, "120")) {
    					$szResult   = substr($szRecvBuf, 133, 1);
    					$szSKey = substr($szRecvBuf, 134, 64);
    				}else{
    					// 전문 응답코드 오류
    					$szResult = "6";
    				}

    			} else if(!strcmp($szReturnCode, "001")) {
    				 //사용자 ID 오류
    			} else if(!strcmp($szReturnCode, "002")) {
    				 //비밀번호 오류
    			} else if(!strcmp($szReturnCode, "003")) {
    				 //사용권한 없음
    			} else if(!strcmp($szReturnCode, "111")) {
    				 //서울신용평가정보 System 장애
    			} else if(!strcmp($szReturnCode, "112")) {
    				 //서울신용평가정보 DataBase 장애
    			} else if(!strcmp($szReturnCode, "229")) {
    				 //전문 Format Type Error
    			} else if(!strcmp($szReturnCode, "301")) {
    				 //전문 종별 코드 오류
    			} else if(!strcmp($szReturnCode, "302")) {
    				 //업무 구분 코드 오류
    			} else if(!strcmp($szReturnCode, "303")) {
    				 //응답 코드 오류
    			} else {
    				 $szResult = "5";
    			}

    		} else {
    			// 전문 응답코드 오류
    			$szResult = "6";
    		}

        if($szResult == "1"){
          $is_valid = true;
        }
        $message = $errors[$szResult]." ($szResult)";

    	}

    }catch (Exception $e) {
        $message = 'Exception: '. $e->getMessage();
    }

    return array('is_valid'=>$is_valid, 'message'=>$message);
  }


  function emailSendingPaypalConfirmation(){
    global $database,$mainframe,$mosConfig_absolute_path;
    $sql = " SELECT * FROM mambophil_users u INNER JOIN mambophil_adv_users au ON u.id = au.id INNER JOIN mambophil_user_extended ue ON u.id = ue.user_id WHERE u.username  = ".$_SESSION["lastusername"];
    $database->setQuery($sql);
    $database ->loadObject($user_info);
    if( count($user_info)>0 && isset($_SESSION['paypal_reg_mail'])){
      require_once( $mosConfig_absolute_path.'/components/com_phpshop/phpshop_parser.php' );
      $user_id = $user_info->user_id;
      $_REQUEST['password']  = $_SESSION["autoship_password"]; //$user_info->password;
      $_REQUEST['order_id']  = $_SESSION['reg_order_id'];
      $_REQUEST['tree_placement']  = $_SESSION["tree_placement"];
      //Sending Email Information
      require_once(CLASSPATH."ps_email.php");
      $ps_email = new ps_email;
      $ps_email->SendRegistrationEmails($user_id, $_REQUEST);

      unset($_SESSION['reg_order_id']);
      unset($_SESSION['tree_placement']);
      unset($_SESSION['paypal_reg_mail']);
    }
  }

  function getorderTotal($autoship_id){
    global $database,$CURRENCY_DISPLAY,$mosConfig_dyanmic_vendor_id;
    require_once(CLASSPATH . 'ps_product.php' );
    $ps_product = new ps_product;
    require_once(CLASSPATH . 'ps_checkout.php');
    $ps_checkout = new ps_checkout;

     $sqlAuto = "SELECT autoship_description,user_info_id,autoship_comments,ship_method_id,autoship_paym, 	"
	  			.	"		DATE_FORMAT(FROM_UNIXTIME(start_dt),GET_FORMAT(DATE,'ISO')) as start_dt, "
	  			.	"		start_dt as start_dt2,use_wallet_payment, "
	  			. "   IF(end_dt=0,'',DATE_FORMAT(FROM_UNIXTIME(end_dt),GET_FORMAT(DATE,'ISO'))) as end_dt, "
	  			. "   eday, leftright_order,MONTHNAME(from_unixtime(start_dt)) AS monthname,YEAR(from_unixtime(start_dt )) AS year,cross_country,cross_shipping_country,rfc_id,user_id "
	  			. "		FROM #__pshop_autoship "
	  			.	"   WHERE autoship_id = ".$autoship_id;

    $database->setQuery($sqlAuto);
    $database->loadObject($resAuto);

    $sqlAutoItems = "SELECT pprice.product_price,pai.product_id, "
      	  					.	" pp.product_name, pai.product_quantity, #TZ__pai.cdate__TZ# , "
      	  					. " #TZ__pai.mdate__TZ#, pai.product_attribute, paos.order_status_name AS order_status																					"
      	  					.	" FROM #__pshop_autoship_item pai				"
      	  					.	" INNER JOIN #__pshop_product pp	ON pai.product_id=pp.product_id		"
      	  					. "INNER JOIN #__pshop_shopper_group sg ON pp.vendor_id=sg.vendor_id"
      	  					.	" INNER JOIN #__pshop_product_price pprice ON pprice.product_id=pp.product_id	"
      	  					. "AND pprice.shopper_group_id=sg.shopper_group_id"
      	  					. " INNER JOIN #__pshop_autoship_order_status paos on pai.order_status = paos.order_status_code "
      							.	"	WHERE pai.product_id = pp.product_id AND																	"
            				.	"       pai.autoship_id = ".$autoship_id. "  and pai.order_status='A' GROUP BY pai.autoship_item_id ";

            $database->setQuery($sqlAutoItems);
            $resAutoItems = $database->loadObjectList();

          if(!empty($resAuto->cross_country)){
               $tax_variable ="SELECT tax_variable FROM mambophil_pshop_country where country_id ='".$_REQUEST['pccountry']."'";
             }else{
               $tax_variable ="SELECT tax_variable FROM mambophil_pshop_country where country_id ='".$_SESSION["wwwuser"]->cntry_join_cd."'";
            }
                                 $database->setQuery($tax_variable);
              	                 $database->loadObject($tax_variables);
              	                 $tax_displaying = $tax_variables->tax_variable;
            $_SESSION['autoList']['shipid'] = $resAuto->user_info_id;

            $shipping_rate_id = $resAuto->ship_method_id;
            if(!empty($shipping_rate_id)){
                $shipping_rate_id = urldecode($shipping_rate_id);
          	    $chunks2 = explode("|",$shipping_rate_id);

              	$shipping_price = $chunks2[2];
              	$products_list = mosgetparam( $_REQUEST, 'product_list', null);
                $d = $_REQUEST;
        	      $d["username"] = $_SESSION['wwwuser']->username;
                $d['shipping_rate_id'] = $shipping_rate_id;
                $d["ship_to_info_id"]  = $ship_to_id;
        	      $cart = null;
                $i = 0;
                foreach($resAutoItems as $resItem){
          	      $cart[$i]["product_id"] = $resItem->product_id;
          	      $cart[$i]['quantity'] = $resItem->product_quantity;
          	      $i++;
                }
          	    $cart["idx"] = $i;
           	    $d['cart'] = $cart;
           	    $order_shipping_vat = $ps_checkout->calc_order_shipping_tax($d);
           	    //var_dump($order_shipping_vat);
                if(!empty($order_shipping_vat)){
       	          $shipping_taxrate = $ps_checkout->calc_order_shipping_tax_rate($d);
       	          //$tax_display = " (+ ".$shipping_taxrate."% VAT)";

                  //start of New Shipping Tax Rule For MEXICO
                  if($_REQUEST['countrycode']=='MEX')
                  {
                    $tax_display = " (impuesto del ".$shipping_taxrate."% ya incluido)";
                  }else
                  {
                    $tax_display = " (+ ".$shipping_taxrate."% $tax_displaying)";
                  }
                  //end of tax rule
                }
            //start of New Shipping Tax Rule For MEXICO
              if($_REQUEST['countrycode']=='MEX'){
                  $shipping_price=$ps_checkout->calc_order_shipping($d);
                }else{
                    $shipping_price =$chunks2[2];
                }
            //end of tax rule
            }
            foreach($resAutoItems as $resItem){

            $product_id = $resItem->product_id;
            // Tax Calculation
            $sess_cart_temp = $_SESSION['cart'];
            unset($_SESSION['cart']); // unset session cart for now and restore later
            $d = $_REQUEST;
    	      $d["username"] = $_SESSION['wwwuser']->username;
            $d['shipping_rate_id'] = $shipping_rate_id;
            $d["ship_to_info_id"]  = $ship_to_id;
    	      $cart = null;
    	      $cart["idx"] = 1;
    	      $cart[0]["product_id"] = $product_id;
    	      $d['cart'] = $cart;
    	      //var_dump($product_id);
    	      $product_taxrate = $ps_product->get_product_taxrate($product_id,0,$d, true, $ship_to_id); // the tax rate call
    	      $product_tax_display = $product_taxrate * 100;
    	      if (!empty($product_taxrate) && MULTIPLE_TAXRATES_ENABLE=='1') {
                $tax_message = " (+ $product_tax_display% ".$tax_displaying.")";
            }
            // restore session cart
            $_SESSION['cart'] = $sess_cart_temp;

            // End Tax Calculation
            $total_tax += (($resItem->product_price*$resItem->product_quantity) * $product_taxrate);

            $producttotal_price=$producttotal_price+($resItem->product_price*$resItem->product_quantity);
  }
  $sub_total = $producttotal_price;

           $cross_shipping_country=$resAuto->cross_shipping_country;
           $cross_countryid=$resAuto->cross_country;

           if(!empty($cross_shipping_country)){
             $cross_country="SELECT country_id,import_tax_amount FROM mambophil_pshop_country where country_3_code='".$cross_shipping_country."'";
             $database->setQuery($cross_country);
             $database->loadObject($cross_countrys);
             if($cross_countrys->country_id!=$cross_countryid){
              $importtax = $ps_checkout->calc_order_shipping_import_tax($cross_countrys->country_id,$sub_total);
             }
           }

            //print_r($_REQUEST);
            if(!empty($importtax)){
              $importtax =$importtax;
              $importtax_display = " (+ ".$cross_countrys->import_tax_amount."% TAX)";
            }else{
              $importtax =0;
            }

            $total_tax = round( $total_tax, 2 );
            $order_total = $producttotal_price + $total_tax + $shipping_price + $order_shipping_vat + $importtax;

            //special rounding for taiwan the taxes are included in the products and the shipping
            if($_REQUEST['countrycode']=='TWN'){
              $order_total = $producttotal_price + $shipping_price + $importtax;
              $total_shipping = $shipping_price + $importtax;
              $values = $CURRENCY_DISPLAY->getRoundValuesTWN($order_total,$total_shipping);
              $order_total = $values['order_total'];
            }

            $order_total = $CURRENCY_DISPLAY->getFullValue($order_total);
            return $order_total;
  }

  function IBOActivation($order_status,$my_user_id,$cntry_join_cd){
      global $database;
      if(!empty($order_status)){

    $sql_status="SELECT order_vol_status_code FROM mambophil_pshop_order_status where order_status_code='".$order_status."' ";
  // echo $sql_status;
        $database->setQuery($sql_status);
        $database->loadObject($newOrderstatus);
    if($newOrderstatus->order_vol_status_code=="C"){
      $block=0;
      $sqlupdate="";
      $sqlupdate .= "Update #__users set block=$block where id=" . $my_user_id.";";
  	  $sqlupdate .= "Update #__adv_users set status_cd='A' where id=" . $my_user_id;
            	  $database->setQuery($sqlupdate);
            	  $database->query_batch();
      }
    }

    if($cntry_join_cd==34){
         $sqlupdateww = "Update #__users set block=0 where id='".$my_user_id."'";
                $database->setQuery($sqlupdateww);
            	  $database->query_batch();
      }

       //Blocking taiwan IBOs whose not signed the paper contract
       if($cntry_join_cd==886) {
        $sql_status="SELECT contract_sign_twn FROM mambophil_adv_users where id='".$my_user_id."'";
        $database->setQuery($sql_status);
        $contract_sign_twn = $database->loadResult();
        if(!$contract_sign_twn) {
             $sqlupdateav = "Update #__users set block=1 where id='".$my_user_id."'";
             $sqlupdateav .= "Update #__adv_users set status_cd='P' where id=" . $my_user_id;
                $database->setQuery($sqlupdateav);
            	  $database->query_batch();
        }
    }
  }
 //function to validate korean home phone. accepted formats: 3-4-4, 2-4-4, 2-3-4
  function korea_phone_validate($phone1,$phone2,$phone3){
     $phone=$phone1."".$phone2."".$phone3;
     if(strlen($phone1)<2 || strlen($phone)<9 || strlen($phone)>11)
       $validate = false;
     return $validate;  
  }

//function to validate korean cell phone. accepted formats: 3-4-4, 3-3-4
  function korea_cell_validate($phone1,$phone2,$phone3){

    if(strlen($phone1)== 3 && strlen($phone2)== 4 && strlen($phone3)== 4)
      $validate = true;
    else if(strlen($phone1)== 3 && strlen($phone2)== 3 && strlen($phone3)== 4)
      $validate = false;
    else
      $validate = false;
    return $validate;
  }

  function sendKoreanIBOSMS($username,$my_user_id){
        global $database;
        //require_once ( $GLOBALS['mosConfig_absolute_path'] .'/webservices/twilio/Services/Twilio.php') ;
        //require_once ( $GLOBALS['mosConfig_absolute_path'] .'/webservices/twilio/bv-info-request-config.php') ;
        require_once ( $GLOBALS['mosConfig_absolute_path'] .'/webservices/twilio_TLS_v12/remote.php');
    // Create the call client.
       // $TWILIO = new Services_Twilio(TWILIO_SID, TWILIO_TOKEN, TWILIO_VERSION);
       /*$sqlIboInfo="SELECT u.kssn_name as name,
           IF(au.cell_phone='--' OR au.cell_phone='','',au.cell_phone) AS cellphone
                     FROM mambophil_users u
                     INNER JOIN mambophil_adv_users au on au.id=u.id
                     WHERE u.username=$username";*/
        $sqlIboInfo="SELECT u.kssn_name as name,
           IF(u.phone_1='--' OR u.phone_1='','',u.phone_1) AS phone_1,DATE_FORMAT(u.registerDate,'%Y-%m-%d') AS RegisterDate
                     FROM mambophil_users u 
                     WHERE u.username=$username";
                     //echo $sqlIboInfo;
        $database->setQuery($sqlIboInfo);
        $database->loadObject($sqlIboInfomation);
        $phone_number=$sqlIboInfomation->phone_1;
       //here adding korean phone country code
        if($phone_number){
          if (strpos($phone_number,'-') !== false){
           $phone_number=str_replace('-','',$phone_number);
           $phone_number = preg_replace('/\s+/', '', $phone_number);
         }
         //echo $phone_number;

          $phone_numbers = substr($phone_number, 0, 1);

          if($phone_numbers!="+"){
           $phone_number = $phone_number;
          }
          
        }
        $name= $sqlIboInfomation->name;
        $registerDate = $sqlIboInfomation->RegisterDate;
        //echo "test".$phone_number;
       if($phone_number){

         //generating the Membership card and getting its link 

         $enzacta_api = new enzAPIFeeder();
         $membeship_cards_info = $enzacta_api->createMembershipCard($username);
      
         if(!empty($membeship_cards_info["short_url"])){
          //$user_name_link = '<a href="'.$membeship_card_link.'" target="_blank"><font color="green"><bold>'.$username.' </bold></font></a>';;
          $user_name_link = $membeship_cards_info["short_url"];
          $token = $membeship_cards_info["token"];
        }else{
          $user_name_link ="";
          $token = $membeship_cards_info["token"];
         }
          //$phone_number ="9567652698";
          //hiding info bank SMS sending
          //$message=" ($name) 님, 엔잭타코리아의 회원이 되신 것을 환영합니다. 회원님의 회원번호는 ($username)이며, 아래를 클릭하시어 회원증을 확인하세요. $user_name_link" ;
          //$sms_result = $enzacta_api->sentMemberhipCardSMS($token,$username,'82',$phone_number,$message);
            //end of info bank SMS sending
          
         //start of Kakao   
          $urlMembershipCard = $user_name_link;
          $urlPolicies = "https://media.enzactainternational.com/media/KR/Legals/KR_P&P_KR_2023.05-sm.pdf";
          $iboName = $name;
          $join_date = $registerDate;
          $kako_phone_number = $phone_number;
          $Source ="MemberShipCard";
          $kakao_input_params = '{"koreanName": "'.$name.'",
                                  "ibo": "'.$username.'",
                                  "phoneNumber": "'.$kako_phone_number.'",
                                  "joinDate": "'.$join_date.'",
                                  "urlMembershipCard": "'.$urlMembershipCard.'", 
                                  "urlPolicies": "'.$urlPolicies.'"}';
          
          $kakao = new kakaoFeeder();
          $order=$kakao->invokeKakoOperations($kakao_input_params,$Source);
         // End of Kakao
          // $last_log_id = $kakao->logKakaoNotifications($Source,$username,$kako_phone_number,$kakao_input_params);
          // $kakao->sentKakaoMembeshipNotification($kako_phone_number,$kakao_input_params,$last_log_id);
       } else {
            $log=3;
            $phone_number = ($phone_number)?$phone_number:'NULL' ;
            iboRegistrationSMSLog($result,$log,$username,$phone_number,$message_response_id,$message);
    }   

  }
  // Log SMS information on ibo rregistration - on db table mambophil_korean_ibo_reg_sms_log
  function iboRegistrationSMSLog($result,$log,$username,$phone_number,$message_response_id,$message){
    global $database;
    
    if($log==1 || $log==2){  
      if(!empty($result->sid)){
        $message_response_id=$result->sid;
        //print_r($result);
      }else{
         $message_response_id=$message_response_id;
       }
      }else{
        $message_response_id ="Invalid Phone Number";
      }
      
  $message_response_id = mysql_real_escape_string($message_response_id);
  $query ="INSERT INTO mambophil_korean_ibo_reg_sms_log
   (id,ibo_number,phone_number,message_response,message,date_created) VALUES  ('','$username','$phone_number','$message_response_id','$message',now())";
  // echo $query; 
  $database->setQuery($query);
  $database->query();
  
}


  function saveshippingaddress($_REQUEST,$values,$d,$country,$my_user_id,$username,$sponsor_number){
        global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY,$mosConfig_invoice_countries;

      $currentUser = $mainframe->getUser();
      require_once(CLASSPATH."ps_user_address.php");

	  $enrollmentType = ($_REQUEST['enrolType']=="Individual") ? "ind" : "bus";

      $d["address_type"] = 'BT';
      $d["address_type_name"] = 'Billing';
	  $d["billing_id_flag"] =1;
      $d["user_id"] = $my_user_id;
      $d["company"] = mosgetparam( $_REQUEST, 'company', '');
      $d["title"] = mosgetparam( $_REQUEST, 'title', '');
      $d["last_name"] = mosgetparam( $_REQUEST, 'last_name', '');
      $d["first_name"] = mosgetparam( $_REQUEST, 'first_name', '');
      $d["middle_name"]  = mosgetparam( $_REQUEST, 'middle_name', '');
      $d["phone_1"] = mosgetparam( $_REQUEST, 'phone_1', '');
      $d["phone_2"] = mosgetparam( $_REQUEST, 'phone_2', '');
      $d["fax"] = mosgetparam( $_REQUEST, 'fax', '');
      $d["address_1"] = mosgetparam( $_REQUEST, 'address_1', '');
      $d["address_2"] = mosgetparam( $_REQUEST, 'address_2', '');
      $d["city"] = mosgetparam( $_REQUEST, 'city', '');
      $d["state"] = mosgetparam( $_REQUEST, 'state', '');
      $d["country"] = mosgetparam( $_REQUEST, 'country', '');
      $d["zip"] = mosgetparam( $_REQUEST, 'zip', '');
      $d["user_email"]=mosgetparam( $_REQUEST, 'email', '');
      $d["district"]=mosgetparam( $_REQUEST, 'district', '');
      $d["section"]=mosgetparam( $_REQUEST, 'section', '');
      $d["lane"]=mosgetparam( $_REQUEST, 'lane', '');
      $d["alley"]=mosgetparam( $_REQUEST, 'alley', '');
	  $d["kssn_name"]=mosgetparam($_REQUEST,$enrollmentType.'kssn_name','');
	  
  $qExtADD = adv_fields_save($d, true, true, $country, false, "#__pshop_user_info", false); 
  $database->setQuery($qExtADD);
        $database->query();
	/*(removed billing country chooser)
	$billingCountry = mosgetparam( $_REQUEST, 'country_chooser_billing_address', '');
	if(isset($_REQUEST['state']) && strlen($_REQUEST['state']) > 0){
		$billingState = mosgetparam( $_REQUEST, 'state', '');
	}else{
		$billingState = mosgetparam( $_REQUEST, 'billingstate_open_field', '');
	}
    $sql_update_billing_flag="UPDATE #__pshop_user_info SET state='".$billingState."',country='".$billingCountry."',billing_id_flag='".$d["billing_id_flag"]."' WHERE user_id=$my_user_id";
	*/
  
  $extra='';
  if( $d["country"]=='KOR'){
    $pos=strpos($qExtADD, 'last_name');
    if(!$pos){
      $extra=", last_name='$d[last_name]' ";
    }
  }
    $sql_update_billing_flag="UPDATE #__pshop_user_info SET billing_id_flag='".$d["billing_id_flag"]."' $extra WHERE user_id=$my_user_id";
    $database->setQuery($sql_update_billing_flag);
    $database->query_batch();

    $q = "SELECT user_info_id from #__pshop_user_info where user_id = ".$my_user_id." and address_type = '".$d["address_type"]."' and address_type_name = '".$d["address_type_name"]."'";
       $database->setQuery($q );
       $database ->loadObject($dbuser);
        $user_info_id= $dbuser->user_info_id;


    if($_REQUEST["shippingaddress"]=="sh_as_bill"){
		
			$d["address_type"] = 'st';
			$d["address_type_name"] = 'Shipping as Billing';
			$d["billing_id_flag"] =0;
			$qShAsBill = adv_fields_save($d, true, true, $country, false, "#__pshop_user_info", false); 
			$database->setQuery($qShAsBill);
			$database->query();
		
	}elseif($_REQUEST["shippingaddress"]=="newship"){
            $d["user_id"] = $my_user_id;

            $d["address_1"] = mosgetparam( $_REQUEST, 'shippingaddress1', '');
            $d["address_2"] = mosgetparam( $_REQUEST, 'shippingaddress2', '');
            $d["address_type"] = 'st';
            $d["address_type_name"] = 'MyFirstShippingAddress';
            $d["last_name"] = mosgetparam( $_REQUEST, 'shipping_last_name', '');
            $d["first_name"] = mosgetparam( $_REQUEST, 'shipping_first_name', '');
            $d["middle_name"] = mosgetparam( $_REQUEST, 'shipping_middle_name', '');
            $d["user_email"] = mosgetparam( $_REQUEST, 'shippingemail', '');
            $d["shipping_email"]=mosgetparam( $_REQUEST, 'shipping_email', '');
            $shipphone_1=mosgetparam( $_REQUEST, 'shipphone_1', '');
            $shipphone_2=mosgetparam( $_REQUEST, 'shipphone_2', '');
            $shipphone_3=mosgetparam( $_REQUEST, 'shipphone_3', '');
            $d["phone_1"] = $shipphone_1;
            if(!empty($shipphone_1) && !empty($shipphone_2) && !empty($shipphone_3)){
                $d["phone_1"] = $shipphone_1.'-'.$shipphone_2.'-'.$shipphone_3;
            }
            $d["extra_field_1"]=mosgetparam( $_REQUEST, 'shipcell_phone', '');

            $d["district"]=mosgetparam( $_REQUEST, 'shippingdistrict', '');
            $d["section"]=mosgetparam( $_REQUEST, 'shippingsection', '');
            $d["lane"]=mosgetparam( $_REQUEST, 'shippinglane', '');
            $d["alley"]=mosgetparam( $_REQUEST, 'shippingalley', '');

			if($_REQUEST["contryid"]=="52"){
            $d["city"] = mosgetparam( $_REQUEST, 'mexshipcityList', '');
            $d["state"] = mosgetparam( $_REQUEST, 'mexshipstates', '');
            $d["country"] = mosgetparam( $_REQUEST, 'country', '');
            $d["zip"] = mosgetparam( $_REQUEST, 'zipcodesmex', '');
            $d["address_2"] = mosgetparam( $_REQUEST, 'mexareaList', '');
            
			}else{
            $d["city"] = mosgetparam( $_REQUEST, 'shipcity', '');
            $d["state"] = mosgetparam( $_REQUEST, 'shipstate', '');
            $d["country"] = mosgetparam( $_REQUEST, 'country', '');
            if($_REQUEST["contryid"]=="1"){
            $d["zip"] = mosgetparam( $_REQUEST, 'ship_zip1', '')." ".mosgetparam( $_REQUEST, 'ship_zip2', '');
            }else{
            $d["zip"] = mosgetparam( $_REQUEST, 'shipzip', '');
            }
			}

			if(isset($_REQUEST["shipping_country_chooser"])){ //enz-441
				$d["country"] = mosgetparam( $_REQUEST, 'shipping_country_chooser', '');
			}
			$d["kssn_name"]=mosgetparam($_REQUEST,'shipkssn',''); //enz-1496

			$qExtADDshipping = adv_fields_save($d, true, true, $country, false, "#__pshop_user_info", false);
			$database->setQuery($qExtADDshipping);
			$database->query();

			$qsh = "SELECT user_info_id from #__pshop_user_info where user_id = ".$my_user_id." and address_type = '".$d["address_type"]."' and address_type_name = '".$d["address_type_name"]."'";
			$database->setQuery($qsh );
			$database ->loadObject($dbuser1);
			$user_info_id= $dbuser1->user_info_id;
     } else  if($_REQUEST["shippingaddress"]=="center"){
        $d["user_id"] = $my_user_id;
        $shipid = mosgetparam( $_REQUEST, 'ship_to_info_id', '');
        $centerquery =  "select * from mambophil_pshop_ibo_centers where id=$shipid";
        $database->setQuery($centerquery);
        $database->loadObject($userinfo);
            $d["address_1"] = $userinfo->address_1;
            $d["address_2"] = $userinfo->address_2;
            $d["address_type"] = 'st';
            $d["address_type_name"] = $userinfo->center_name;
            $d["last_name"] = $userinfo->last_name;
            $d["first_name"] = $userinfo->manager_name;
            $d["middle_name"] = '';
            $d["user_email"] = '';
            $d["shipping_email"]='';
            $d["city"] = $userinfo->city;
            $d["state"] = $userinfo->state;
            $d["country"] = mosgetparam( $_REQUEST, 'country', '');
            $d["phone_1"] = $userinfo->phone_1;
            $d["phone_2"] = $userinfo->phone_2;
            $d["fax"] = $userinfo->fax;
            $d["zip"] = $userinfo->zip;


    $qExtADDshipping = adv_fields_save($d, true, true, $country, false, "#__pshop_user_info", false);
           $database->setQuery($qExtADDshipping);
        $database->query();

      $qsh = "SELECT user_info_id from #__pshop_user_info where user_id = ".$my_user_id." and address_type = '".$d["address_type"]."' and address_type_name = '".$d["address_type_name"]."'";
       $database->setQuery($qsh );
       $database ->loadObject($dbuser1);
        $user_info_id= $dbuser1->user_info_id;
     }

      $sql_billi="SELECT user_info_id from #__pshop_user_info where user_id = ".$my_user_id." and billing_id_flag=1";
                   $database->setQuery($sql_billi );
                   $database ->loadObject($sql_billi_1);
                   $billing_address_info_id =$sql_billi_1->user_info_id;



     $_REQUEST["user_info_id"]  = $user_info_id;
     $_REQUEST["billing_address_info_id"] = $billing_address_info_id;
  }


   function duplicateaddressandautoships($my_user_id,$prefs){ 

       global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY,$mosConfig_invoice_countries;



        $address_copy="INSERT INTO mambophil_pshop_user_info(user_id,address_type,address_type_name,company,title,last_name,first_name,middle_name,phone_1,phone_2,
                       fax,address_1,address_2,city,state,country,zip,user_email,cdate,mdate,perms,officepickup_flag)

                      SELECT $my_user_id,address_type,address_type_name,company,title,last_name,first_name,middle_name,phone_1,phone_2,
                      fax,address_1,address_2,city,state,country,zip,user_email,cdate,mdate,perms,officepickup_flag FROM mambophil_pshop_user_info WHERE user_id='".$prefs."'";

                           $database->setQuery($address_copy);
	                         $database->query_batch();



       $sql_autoshidb="SELECT autoship_id,user_info_id FROM mambophil_pshop_autoship where user_id='".$prefs."'";
                       $database->setQuery($sql_autoshidb );
                       $resAutoduplicateItems = $database->loadObjectList();



            foreach($resAutoduplicateItems as $sqlautohshipitems)
             {
              if(!empty($sqlautohshipitems->autoship_id)){


              $sql_address ="SELECT * FROM mambophil_pshop_user_info where user_info_id='".$sqlautohshipitems->user_info_id."'";
                      $database->setQuery($sql_address);
                      $database->loadObject($sql_addressss);


                $addres_forautoship="SELECT * FROM mambophil_pshop_user_info where user_id='".$my_user_id."'
                                     AND address_type='".$sql_addressss->address_type."' AND address_type_name='".$sql_addressss->address_type_name."'
                                     AND address_1 ='".$sql_addressss->address_1."' AND address_2='".$sql_addressss->address_2."' AND city='".$sql_addressss->city."' AND state ='".$sql_addressss->state."' AND country = '".$sql_addressss->country."' AND zip='".$sql_addressss->zip."'";
                         //echo $addres_forautoship;
                            $database->setQuery($addres_forautoship);
                            $database->loadObject($addres_user_info);
                            $user_info_id =$addres_user_info->user_info_id;

                            if(empty($user_info_id)){
                              $user_info_s ="SELECT * FROM mambophil_pshop_user_info where user_id='".$my_user_id."' LIMIT 0,1";
                                                $database->setQuery($user_info_s);
                                                $database->loadObject($user_info_ss);
                                                $user_info_id=$user_info_ss->user_info_id;
                                                //echo "thiscase";
                             }

               $sql_autoship_insert="INSERT INTO mambophil_pshop_autoship(user_id,user_info_id,order_status,cdate,mdate,eday,start_dt,end_dt,ship_method_id,customer_note,leftright_order,ip_address,placed_user_id,autoship_description,autoship_comments,register_id,
                                    rejected_count,cross_country,cross_shipping_country,rfc_id,einvoice_status,use_wallet_payment)
                                    SELECT $my_user_id,$user_info_id,order_status,cdate,mdate,eday,start_dt,end_dt,ship_method_id,customer_note,leftright_order,
                                    ip_address,placed_user_id,autoship_description,autoship_comments,register_id,
                                    rejected_count,cross_country,cross_shipping_country,rfc_id,einvoice_status,use_wallet_payment
                                    FROM mambophil_pshop_autoship WHERE autoship_id='".$sqlautohshipitems->autoship_id."'";
                            //echo $sql_autoship_insert;
                           $database->setQuery($sql_autoship_insert);
	                         $database->query_batch();


              $qSel = "SELECT MAX(autoship_id) AS autoship_id FROM #__pshop_autoship WHERE user_id=".$my_user_id;
                  //echo $qSel;
                  $database->setQuery($qSel);
                  $database->loadObject($objLastIdx);
                  $ret_autoship_id = $objLastIdx->autoship_id;


             $sql_autoship_item="INSERT INTO mambophil_pshop_autoship_item(autoship_id,product_id,product_quantity,order_status,cdate,mdate,product_attribute)
                                 SELECT $ret_autoship_id,product_id,product_quantity,order_status,cdate,mdate,product_attribute
                                 FROM mambophil_pshop_autoship_item   WHERE autoship_id='".$sqlautohshipitems->autoship_id."'";
                           $database->setQuery($sql_autoship_item);
	                         $database->query_batch();




              $sql_autoship_payment="INSERT INTO mambophil_pshop_autoship_payment
                                    (autoship_id,payment_method_id,order_payment_code,order_payment_number,order_payment_expire,order_payment_name,order_payment_status,
                                     creditcard_code,cdate,mdate,user_info_id,payment_type,cuotas,financial_months,order_payment_email)
                                     SELECT $ret_autoship_id, payment_method_id,order_payment_code,order_payment_number,order_payment_expire,order_payment_name,order_payment_status,
                                     creditcard_code,cdate,mdate,IF(user_info_id=1,user_info_id,user_info_id),payment_type,cuotas,financial_months,order_payment_email
                                     FROM mambophil_pshop_autoship_payment WHERE autoship_id='".$sqlautohshipitems->autoship_id."'";
                           $database->setQuery($sql_autoship_payment);
	                         $database->query_batch();

               }
              }

             //saving the correct billing address for autoships payments

               $sql_payment_address="SELECT a.autoship_id,ap.autoship_payment_id,a.user_id,ap.user_info_id  FROM mambophil_pshop_autoship a
                                    INNER JOIN mambophil_pshop_autoship_payment ap ON ap.autoship_id =a.`autoship_id`
                                    WHERE a.user_id ='".$my_user_id."' AND ap.user_info_id<>1";
                      $database->setQuery($sql_payment_address);
                      $respayment_address = $database->loadObjectList();

                      foreach($respayment_address as $respayments) {

                        $sql_address_1 ="SELECT * FROM mambophil_pshop_user_info where user_info_id='".$respayments->user_info_id."'";
                        $database->setQuery($sql_address_1);
                        $database->loadObject($sql_addressss_1);


                $addres_forautoships="SELECT * FROM mambophil_pshop_user_info where user_id='".$my_user_id."'
                                     AND address_type='".$sql_addressss_1->address_type."' AND address_type_name='".$sql_addressss_1->address_type_name."'
                                     AND address_1 ='".$sql_addressss_1->address_1."' AND address_2='".$sql_addressss_1->address_2."' AND city='".$sql_addressss_1->city."' AND state ='".$sql_addressss_1->state."' AND country = '".$sql_addressss->country."' AND zip='".$sql_addressss_1->zip."'";
                            $database->setQuery($addres_forautoships);
                            $database->loadObject($addres_user_infos);
                            $user_info_ids =$addres_user_infos->user_info_id;

                            if(empty($user_info_ids)){
                              $user_info_ids="1";
                             }


                         $sql_update ="UPDATE mambophil_pshop_autoship_payment SET user_info_id='".$user_info_ids."'
                                       WHERE autoship_payment_id='".$respayments->autoship_payment_id."' AND autoship_id ='".$respayments->autoship_id."'" ;
                                       $database->setQuery($sql_update);
                                       $database->query_batch();
                      }
   }
   function getPaymentMethodDetails($methodId){
     global $database;
     if($methodId){
       $SQL="SELECT * FROM mambophil_pshop_payment_method where payment_method_id='".$methodId."'";
       $database->setQuery($SQL);
       $database->loadObject($result);
       return $result;
     }
   }
  
  function PrintEditionOfIBOApplication($Last_Username){

     global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$CURRENCY_DISPLAY,$mosConfig_invoice_countries;

            $sql="SELECT u.country FROM mambophil_users u
                  INNER JOIN mambophil_pshop_orders o on o.user_id=u.id
                  WHERE o.order_type=1 AND u.username='".$Last_Username."'";
                        $database->setQuery($sql);
                        $database->loadObject($value);
                $_SESSION["USER_PRINT_OPT"]=$Last_Username;
                if($value->country=="TWN"){
                  ?>
                 <div align="center">
               <input type="button" class="btn" id="btn_save" value="<?echo _PRINT_ENROLL_DETAILS;?>" onclick="window.open('index.php?option=com_ibo&Itemid=3955&step_number=6001&action_number=53&no_menu=1','popUpWindow','scrollbars=yes,menubar=no'); return false;">
                </div>
                  <?
                }
   }

   function PrintIndependentBusinessOwnerApplication(){
         global $database,$mosConfig_absolute_path,$mosConfig_live_site,$mainframe,$PHPSHOP_LANG,$mosConfig_invoice_countries;
         $show_credit_card = true; // flag for show the credit card
         require_once( $mosConfig_absolute_path.'/components/com_phpshop/phpshop_parser.php' );
         $PHPSHOP_LANG =& new phpShopLanguage();

      require_once('administrator/components/com_phpshop/classes/ps_product.php' );
      require_once('administrator/components/com_phpshop/classes/ps_checkout.php' );
      require_once( 'administrator/components/com_phpshop/classes/ps_database.php' );
      require_once( 'administrator/components/com_phpshop/classes/class_currency_display.php' );
      require_once( 'administrator/components/com_phpshop/phpshop.cfg.php' );
      $CURRENCY_DISPLAY = new CurrencyDisplay();
      $ps_product = new ps_product;
      $ps_checkout = new ps_checkout;

          $USerinfo="SELECT u.*,u.id as userid,au.*,ue.* FROM mambophil_users u
                    INNER JOIN mambophil_adv_users au on u.id=au.id
                    INNER JOIN mambophil_user_extended ue ON ue.user_id=u.`id`
                    WHERE  u.username='".$_SESSION["USER_PRINT_OPT"]."'";
                        $database->setQuery($USerinfo);
                        $database->loadObject($uservalues);
          $SponsorInfo="SELECT * FROM mambophil_users u where u.username='".$uservalues->user3."'";           
                        $database->setQuery($SponsorInfo);
                        $database->loadObject($SponsorInfovalues);

                        $placement=explode('-',$uservalues->user5);

          $PlaceMentInfo="SELECT * FROM mambophil_users u where u.username='".$placement[0]."'";
                        $database->setQuery($PlaceMentInfo);
                        $database->loadObject($PlacementInfovalues);

           $sql_order ="SELECT * FROM mambophil_pshop_orders where user_id='".$uservalues->userid."' AND order_type=1";
                        $database->setQuery($sql_order);
                        $database->loadObject($OrderInfovalues);

            $shipping_address="SELECT * FROM mambophil_pshop_user_info where user_info_id='".$OrderInfovalues->user_info_id."'";
                         $database->setQuery($shipping_address);
                         $database->loadObject($ShippingInfovalues);

             $order_items="SELECT * FROM mambophil_pshop_order_item ot
                           INNER JOIN mambophil_pshop_product p on p.product_id=ot.product_id
                           WHERE ot.order_id='".$OrderInfovalues->order_id."' ORDER BY item_seq_nbr ASC";

                           $database->setQuery($order_items);
                           $order_itemsvalues=$database->loadObjectList();
             $tx_rate="SELECT tax_rate,special_rounding_tax FROM mambophil_pshop_tax_rate where vendor_id='".$OrderInfovalues->vendor_id."'";
                       $database->setQuery($tx_rate);
                        $database->loadObject($tx_rateValues);
                        $taxrate=($OrderInfovalues->vendor_id == 23) ? ($tx_rateValues->special_rounding_tax*100) : ($tx_rateValues->tax_rate*100);
            //Shipping method
            $data = explode("|", $OrderInfovalues->ship_method_id);
            $shipping_rate_id = (isset($data[count($data)-1])) ? (int)$data[count($data)-1] : 0;
            $sql_shipping = "SELECT sc.is_pick_up FROM mambophil_pshop_shipping_rate sr
                             INNER JOIN mambophil_pshop_shipping_carrier sc ON sr.shipping_rate_carrier_id = sc.shipping_carrier_id
                             WHERE shipping_rate_id = '".$shipping_rate_id."'";
            $database->setQuery($sql_shipping);
            $database->loadObject($shipping_method);


            $payment_method="SELECT *, CAST(DECODE(order_payment_number, '".ENCODE_KEY."') AS char) as payment_code FROM mambophil_pshop_order_payment op
                             INNER JOIN mambophil_pshop_payment_method pm on
                             pm.payment_method_id=op.payment_method_id where op.order_id='".$OrderInfovalues->order_id."'";

                             $database->setQuery($payment_method);
                             $Payment_methodValues = $database->loadObjectList();


           $tncSql = "select tnc_filepath from mambophil_t_and_c where countrycode='TW'";

                  $database->setQuery($tncSql);
                  $database ->loadObject($filepath);

                 $tnc = _NEWREGISTRATION_TERMS_CONDITIONS;
                 if($filepath->tnc_filepath){
                  $tnc .='<a href="https://www.enzacta.com/customer'.$filepath->tnc_filepath.'" target="_blank"><font size="2">'._POLICY_PROCEDURE.'</font></a>';
                 }else{
                  $tnc .='<a href="https://www.enzacta.com/customer/media/US/Legals/PoliciesProceduresUS_140627.pdf" target="_blank"><font size="2">'._POLICY_PROCEDURE.'</font></a>';
                 }


                        ?>
         <!doctype html>
      <html lang="en">
        <head>
          <title>Independent Business Owner Applicantion & Agreement</title>
          <!--meta info-->
          <meta charset="utf-8">
          <!--theme css-->
           <style>
                                 body{text-align: left!important;}
                                 *{font-family: Helvetica, Arial, Sans-Serif;}
                                 p {margin: 4.5px 0; padding-top: 8px;}
                                 h4, h5{padding-left: 15px;margin: 5px 0;}
                                 h2,h3{color: black;margin:5px;}
                                 /*h4 {padding-top: 5px;}*/
                                 .nbsp{padding: 0 7px;}
                                 .page1{min-height: 1300px;}
                                 .p_top_2{padding-top: 2px!important}
                                 .p_top_10{padding-top: 10px;}
                                 .p_top_30{padding-top: 30px;}
                                 .p_bot_10{padding-bottom: 10px;}
                                 .p_bot_30{padding-bottom: 30px;}
                                 .bor_bot{border-bottom: 1px solid #000000;}
                                 .bor_bot label{font-family: sans-serif;font-weight: 700;color: #000;font-size: 12px;padding:0 5px;}
                                 .mar_sids_20{margin-left: 20px; margin-right: 20px;}
                                 .bor_full{border: 1px solid #000000;}
                                 .bor_side{border-left: 1px solid #000000;border-right: 1px solid #000000;}
                                 .title{font-style: italic;font-size: 12px;margin-left: 0!important;}
                                 .fon_size_9{font-size: 9px!important;}
                                 .terms{font-size: 11px;text-align: justify;width: 45%;margin: 0 20px;}
                                 .terms_wid_mar{ }
                                 .t_align_r{text-align: right;}
                                 .t_align_c{text-align: center;}
                                 .t_align_l{text-align: left;}
                                 .t_align_j{text-align: justify;}
                                 .v_aling_bl{vertical-align: baseline;}
                                 .v_aling_bo{vertical-align: bottom;}
                                 .no_bor_bot{border-bottom: 0}
                                 .w_70{width: 70%;}
                                 .w_68{width: 68%;}
                                 .w_10{width: 10%;}
                                 .w_15{width: 15%;}
                                 .w_30{width: 30%}
                                 .w_50{width: 50%;}
                                 .w_95{width: 95%;}
                                 .w_100p{width: 100px;}
                                 .p_left_15{padding-left: 15px;}
                                 .flo_lef{float: left;}
                                 .cho_one{width: 5%;float: left;}
                                 .bac_gray{background: #CACACA;}
                                 .fon_size_14{font-size: 13px;}
                                 .f_size_11{font-size: 11px!important;}
                                 .margin_0{margin: 0;}
                                 .p_rigth_7{padding-right: 7px;}
                                 .pad_5_10{padding: 0 5px!important;}
                                 .fon_siz_10{font-size: 10px;}
                                 .fon_siz_11{font-size: 11px;}
                                 .center{margin:0 auto;}
                                 .id_box{width: 240px;height: 65px;float:left;border: 4px;border-style: dotted;display: table;margin: 5px 85px;}
                                 .id_box h2{display: table-cell;text-align: center;vertical-align: middle;}
                                 .m_left_15{margin-left:15px;}
                                 .pad_0{padding: 0;}
                                 .p_top_15{padding-top: 15px;}
                                 .table_page2{margin: 0 auto;padding: 10px 0 0;}
                                 .table_page2 td{ border: 1px #000 solid;}
                                 .bor_bot span{margin: 0 5px;}

                                 @media print{
                                 @page {size: portrait;  size: A4;margin: 0;}
                                 body{text-align: left!important;}
                                 *{font-family: Helvetica, Arial, Sans-Serif;}
                                 p {margin: 4.5px 0; padding-top: 8px;}
                                 h4, h5{padding-left: 15px;margin: 5px 0;}
                                 h2,h3{color: black;margin:5px; }
                                 /*h4 {padding-top: 5px;}*/
                                 .nbsp{padding: 0 7px;}
                                 .page1{min-height: 1300px;}
                                 .p_top_2{padding-top: 2px!important}
                                 .p_top_10{padding-top: 10px;}
                                 .p_top_30{padding-top: 30px;}
                                 .p_bot_10{padding-bottom: 10px;}
                                 .p_bot_30{padding-bottom: 30px;}
                                 .bor_bot{border-bottom: 1px solid #000000;}
                                 .bor_bot label{font-family: sans-serif;font-weight: 700;color: #000;font-size: 12px;padding:0 5px;}
                                 .mar_sids_20{margin-left: 20px; margin-right: 20px;}
                                 .bor_full{border: 1px solid #000000;}
                                 .bor_side{border-left: 1px solid #000000;border-right: 1px solid #000000;}
                                 .title{font-style: italic;font-size: 12px;margin-left: 0!important;}
                                 .fon_size_9{font-size: 9px!important;}
                                 .terms{font-size: 11px;text-align: justify;width: 45%;margin: 0 20px;}
                                 .terms_wid_mar{ }
                                 .t_align_r{text-align: right;}
                                 .t_align_c{text-align: center;}
                                 .t_align_l{text-align: left;}
                                 .t_align_j{text-align: justify;}
                                 .v_aling_bl{vertical-align: baseline;}
                                 .v_aling_bo{vertical-align: bottom;}
                                 .no_bor_bot{border-bottom: 0}
                                 .w_70{width: 70%;}
                                 .w_68{width: 68%;}
                                 .w_10{width: 10%;}
                                 .w_15{width: 15%;}
                                 .w_30{width: 30%}
                                 .w_50{width: 50%;}
                                 .w_95{width: 95%;}
                                 .w_100p{width: 100px;}
                                 .p_left_15{padding-left: 15px;}
                                 .flo_lef{float: left;}
                                 .cho_one{width: 5%;float: left;}
                                 .bac_gray{background: #CACACA;}
                                 .fon_size_14{font-size: 13px;}
                                 .f_size_11{font-size: 11px!important;}
                                 .margin_0{margin: 0;}
                                 .p_rigth_7{padding-right: 7px;}
                                 .pad_5_10{padding: 0 5px!important;}
                                 .fon_siz_10{font-size: 10px;}
                                 .fon_siz_11{font-size: 11px;}
                                 .center{margin:0 auto;}
                                 .id_box{width: 240px;height: 65px;float:left;border: 4px;border-style: dotted;display: table;margin: 5px 85px;}
                                 .id_box h2{display: table-cell;text-align: center;vertical-align: middle;}
                                 .m_left_15{margin-left:15px;}
                                 .pad_0{padding: 0;}
                                 .p_top_15{padding-top: 15px;}
                                 .table_page2{margin: 0 auto;padding: 10px 0 0;}
                                 .table_page2 td{ border: 1px #000 solid;}
                                 .bor_bot span{margin: 0 5px;}
                                 }
                                 }
                                 }
                              </style>
        </head>
         <body style="height: 942px;width: 895px;margin-left: auto;margin-right: auto;" >
          <div class="page1">
          <div class="w_30 flo_lef ">
            <img src="https://wwwmx.enzacta.com/Customer/templates/JavaBeanMexico/images/EnzactaLogo.gif" alt="" style="width: 100px">
          </div>
          <div class="t_aligr_r w_68 flo_lef ">
            <h3 class="margin_0" style="color: black;font-size: 18px;padding-top: 20px;"><?= TITTLE?></h3>
            <p style="padding-top: 2px;"><?= SUB_TIT_REQ_INF?></p>
          </div>
          <table   align="center" style="width: 90%; line-height: 0px; border-collapse: collapse;" class="fon_size_14"></table>
          <h4><?= TIT_FIRS_SEC?></h4>
          <table   align="center" style="width: 95%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
                  <tr>
                      <td></td>
                      <td class="bor_bot">
                        <p><?= _IBO_NUMBER?></p>
                        <p class="title"><?= $uservalues->username; ?></p>
                      </td>
                      <td class="nbsp"></td>  
                      <td class="bor_bot">
                          <p><?= APPLI_CHIN_NAME?></p>
                          <p class="title"><?= $uservalues->kssn_name; ?></p>
                      </td>
                      <? if($uservalues->enroll_type=="Business"){ ?>
                      <td class="nbsp"></td>
                      <td class="bor_bot">
                          <p><?= _BUSINESS_FIRST_NAME?></p>
                          <p class="title"><?= $uservalues->business_name; ?></p>
                      </td>
                      <?php } ?>
                      <td class="nbsp"></td>              
                      <td class="bor_bot">
                            <p><?= APPLI_EN_NAME?></p>
                            <p class="title">
                              <?php
                                echo implode(' ', array($uservalues->first_name, $uservalues->last_name));
                                if($uservalues->coapp_name){
                                  echo "<br />" . $uservalues->coapp_name;
                                }
                              ?>
                            </p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                        <p><?= APPLI_SOCI_SEC_NUM?></p>
                        <p class="title"><?echo $uservalues->tax_id;?></p>
                      </td>
                  </tr>
          </table>
          <table   align="center" style="width: 95%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
                  <tr>                  
                      <td colspan="6" class="bor_bot">
                            <p><?= APPLI_RESI_ADDRES?></p>
                            <?php
                              $address = array();
                              $address[] = $uservalues->zip;
                              $address[] = implode(' ', array($uservalues->address_1, $uservalues->address_2));
                              if(!empty($uservalues->city)){
                                $address[] = $uservalues->city;
                              }
                              if(!empty($uservalues->district)){
                                $address[] = $uservalues->district;
                              }
                              if(!empty($uservalues->section)){
                                $address[] = $uservalues->section;
                              }
                              if(!empty($uservalues->lane)){
                                $address[] = $uservalues->lane;
                              }
                              if(!empty($uservalues->alley)){
                                $address[] = $uservalues->alley;
                              }
                            ?>
                            <p class="title"><?= implode(',', $address) ?></p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                            <p><?= APPLI_PHON_NUM?></p>
                            <p class="title"><?= $uservalues->phone_1; ?></p>
                         </td>
                  </tr>
          </table>
          <table   align="center" style="width: 95%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
                  <tr>
                    <td colspan="4" class="bor_bot">
                            <p><?= APPLI_MAIL_ADDRES?></p>
                            <?php
                              $address = array();
                              $address[] = $ShippingInfovalues->zip;
                              $address[] = implode(' ', array($ShippingInfovalues->address_1, $ShippingInfovalues->address_2));
                              if(!empty($ShippingInfovalues->city)){
                                $address[] = $ShippingInfovalues->city;
                              }
                              if(!empty($ShippingInfovalues->district)){
                                $address[] = $ShippingInfovalues->district;
                              }
                              if(!empty($ShippingInfovalues->section)){
                                $address[] = $ShippingInfovalues->section;
                              }
                              if(!empty($ShippingInfovalues->lane)){
                                $address[] = $ShippingInfovalues->lane;
                              }
                              if(!empty($ShippingInfovalues->alley)){
                                $address[] = $ShippingInfovalues->alley;
                              }

                            ?>
                            <p class="title"><?= implode(',', $address) ?></p>
                         </td>
                         <td class="bor_bot">
                            <input id="ADD_AS_RESIDENCE" type="radio"><?= APPLI_MAIL_ADDRES_SAME?>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                            <p><?= APPLI_EMAIL_ADDRES?></p>
                            <p class="title"><?= $uservalues->email; ?></p>
                         </td>
                  </tr>
          </table>
          <table   align="center" style="width: 95%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
                  <tr>
                    <td colspan="4" class="bor_bot">
                            <p><?= APPLI_BUS_NAME?></p>
                            <p class="title"><?= $uservalues->business_name;?></p>
                         </td>
                         <td class="nbsp"></td>
                         <td colspan="3" class="bor_bot">
                            <p><?= APPLI_BUS_ID?></p>
                            <p class="title"><?echo ($uservalues->business_name != '') ? $uservalues->tax_id : '';?></p>
                         </td>
                  </tr>
              </table>
              <h4><?= TIT_SECOND_SEC?></h4>
          <table   align="center" style="width: 90%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
              <tr>
              <td class="bor_bot">
                            <p class="pad_0"><?= SPONS_IBO_NUM?></p>
                            <p class="title"><?= $uservalues->user3;?></p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                            <p class="pad_0"><?= SPONS_FULL_NAME?></p>
                            <p class="title"><?= $SponsorInfovalues->last_name;?> <?= $SponsorInfovalues->first_name; ?></p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                            <p class="pad_0"><?= SPONS_PHON_NUM?></p>
                            <p class="title"><?= $SponsorInfovalues->phone_1;?></p>
                         </td>
              </tr>
          </table>
          <h4><?= TIT_SECOND_SEC?></h4>
                   <table   align="center" style="width: 90%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
                      <tr>
                         <td class="bor_bot">
                            <p class="pad_0"><?= SPONS_PLAC_IBO_NUM?></p>
                            <p class="title"><?= $placement[0];?></p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                            <p class="pad_0"><?= SPONS_PLAC_FULL_NAME?></p>
                            <p class="title"><?= $PlacementInfovalues->last_name; ?> <?= $PlacementInfovalues->first_name;  ?></p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                            <p class="pad_0"><?= TEXT_SPONS_PLAC?></p>
                            <?
              if($uservalues->user6=="L"){
                  $radioleftcheck="checked";
              }else{
                  $radiorightcheck="checked";
              } ?>
                            <input type="radio" <? echo $radioleftcheck; ?> disabled><label for="ibo_appli_place_left"><?= TEXT_SPONS_PLAC_SIDE_LEFT?></label><input type="radio" <? echo $radiorightcheck; ?> disabled><label for="ibo_appli_place_right"><?= TEXT_SPONS_PLAC_SIDE_RIGTH?></label>
                         </td>
                      </tr>
                   </table>
          <h4><?= TIT_THIR_SEC?></h4>
          <!--<h5><?= SUB_TIT_THIR_SEC?></h3>-->
          <div class="cho_one p_left_15">
            <table style="background: #CACACA;border: 1px #CACACA solid;width: 70px;">
              <tr>
                <td ><p class="pad_5_10"><?= TEXT_BUSIN_CHOOS_ONE?></p></td>
              </tr>
            </table>
            <table style="background: #CACACA;border: 1px #CACACA solid;margin-top: 90%;width: 70px;">
                         <tr>
                            <td >
                               <p class="pad_5_10"><?= TEXT_BUSIN_SHIPPIN_TABLE?></p>
                            </td>
                         </tr>
                      </table>
          </div>
          <div>
            <table   align="center" style="width: 85%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
              <tr class="t_align_c">
                <td></td>
                <td><p><?= _quantity?></p></td>
                <td><p><?= TEXT_BUSIN_COST?></p></td>
                <td><!--<?= TEXT_BUSIN_INVES?>--></td>
              </tr>
          <? foreach($order_itemsvalues as $product_results){ ?>
              <?
                $tax_included_fl = $ps_product->get_field($product_results->product_id,"tax_included_fl");
                if ($tax_included_fl == '0') {
                  $message = " (+$taxrate% TAX)";
                }else{
                  $message = " "._VAT_INCLUDED." ($taxrate%)";
                }
              ?>
              <tr class="bor_bot">
                <td class="w_100p" > <input id="BUSIN_IBO_STAN_PLAN" type="radio"  checked disabled ><?= $product_results->product_attribute;?></td>
                 <td class="t_align_c bor_side w_100p"><p><?= $product_results->product_quantity;?></p></td>
                <td class="t_align_c bor_side"><p><?= $CURRENCY_DISPLAY->getFullValue($product_results->product_item_price, 'TWN'); ?></p></td>
                <td colspan="2" class="t_align_c  bor_side"><p><?= $CURRENCY_DISPLAY->getFullValue($product_results->product_item_price, 'TWN') .$message;?></p></td>
              </tr>
              <? } ?>

<?

            $country_look_up="SELECT vendor_country FROM mambophil_pshop_vendor where vendor_id=$OrderInfovalues->vendor_id";
            $database->setQuery($country_look_up);
            $database->loadObject($country_look_result);
            $country_pickup_look= $country_look_result->vendor_country;
            $language_Text=$ps_checkout->pickupcenterlookup($OrderInfovalues->ship_method_id,$country_pickup_look);
?>
              <tr>
                <td class="title"><p></td>
                <td></td>
                <td class="t_aligr_r"><p class="p_rigth_7"><?= $language_Text[0]?></p></td>
                <td class="t_align_c bor_full"><p><?= $CURRENCY_DISPLAY->getFullValue($OrderInfovalues->order_shipping, 'TWN');?></p></td>

              </tr>

              <tr>
                <td></td>
                <td></td>
                <td class="t_aligr_r"><p class="p_rigth_7"><?= TEXT_BUSIN_SUB_TOTAL?></p></td>
                <td class="t_align_c bor_full"><p><?= $CURRENCY_DISPLAY->getFullValue($OrderInfovalues->order_subtotal, 'TWN');?></p></td>

              </tr>

              <tr>
                <td><div style="width: 165px;float: left;font-size: 10px;">
                                  <input type="radio" disabled <?echo($shipping_method->is_pick_up == "1") ? "checked" : ""?>> <?= SHIPP_RAD_OFFICE?>
                                  <input type="radio" disabled <?echo($shipping_method->is_pick_up == "0") ? "checked" : ""?>> <?= SHIPP_RAD_OTHER?>
                                </div>
                                <div class="bor_bot" style="width: 200px;float: left;padding-top: 2px;"><p class="title"><?echo ($shipping_method->is_pick_up == "0") ? implode(',', $address) : "" ?></p></div>
                                <div style="float: left;"><p style="margin-left: 10px;font-size: 10px;"> <?= SHIPP_RAD_OTHER_ADD_TXT?></p></div></td>
                <td></td>
                <td class="t_aligr_r"><p class="p_rigth_7"><?= $PHPSHOP_LANG->_PHPSHOP_ORDER_PRINT_TOTAL_TAX_VAT;?></p></td>
                <td class="t_align_c bor_full"><p><?= $CURRENCY_DISPLAY->getFullValue($OrderInfovalues->order_tax, 'TWN');?> </p></td>
              </tr>

              <tr>
                <td colspan="2"> <p style="font-size: 10px;white-space: pre;"><?echo($shipping_method->is_pick_up == "0") ? SHIPP_NOTE_TXT : ""?></p></td>
                <td class="t_aligr_r"><p class="p_rigth_7"><?= TEXT_BUSIN_TOTAL_INVES?></p></td>
                <td class="t_align_c bor_full"><p><?= $CURRENCY_DISPLAY->getFullValue($OrderInfovalues->order_total, 'TWN');?></p></td>
              </tr>
            </table>
          </div>
          <h4><?= TIT_FOUR_SEC?></h4>
                   <table align="center" style="width: 90%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
                      <?php
                      foreach($Payment_methodValues as $payment): ?>
                      <?
                        $credit_card_number = $payment->payment_code;
                        if (!$show_credit_card) {
                          $credit_card_number = substr_replace($credit_card_number, "************", 0, -4);
                        }
                      ?>
                      <tr class="bor_bot t_align_l">
                        <td><input type="radio" checked disabled><?= $payment->payment_method_name ?></td>
                      </tr>
                        <?php if(!$payment->is_creditcard): ?>
                        <tr>
                          <td class="bor_bot">
                            <p><?= _AUTOSHIP_PAYINFO_PAYAMOUNT?></p>
                            <p class="title"><?= $CURRENCY_DISPLAY->getFullValue($payment->order_payment_amount, 'TWN'); ?></p>
                          </td>
                        </tr>
                        <?php else: ?>
                        <tr>
                          <td class="bor_bot">
                              <p><?= TEXT_PAYM_NAME?></p>
                              <p class="title"><?= $payment->order_payment_name ?></p>
                          </td>
                          <td class="bor_bot" colspan="4">
                              <p><?= TEXT_PAYM_CARD_TYPE?></p>
                              <input id="credit_card_type_visa" type="radio" <? echo ($payment->creditcard_code == 'VISA') ? 'checked' : ''; ?> disabled>
                                <label for="credit_card_type_visa" ><?= TEXT_PAYM_METH_CARD_VISA?></label>
                              <input id="credit_card_type_master" type="radio" <? echo ($payment->creditcard_code == 'MC') ? 'checked' : ''; ?> disabled>
                                <label for="credit_card_type_master" ><?= TEXT_PAYM_METH_CARD_MASTR_CARD?></label>
                              <input id="credit_card_type_jcb" type="radio" <? echo ($payment->creditcard_code == 'jcb') ? 'checked' : ''; ?> disabled>
                                <label for="credit_card_type_master" ><?= TEXT_PAYM_METH_CARD_JCB?></label>
                          </td>
                          </tr>
                          <tr>
                            <td colspan="2" class="bor_bot">
                                <p><?= TEXT_PAYM_METH_CRED_CARD_NUM?></p>
                                <p class="title"><?= $credit_card_number ?></p>
                            </td>
                            <td class="bor_bot">
                                <p><?= TEXT_PAYM_METH_EXP_DATE?></p>
                                <p><?= date('M', $payment->order_payment_expire); ?><span class="title"><?= TEXT_PAYM_METH_MONTH?></span><span><?= date('Y', $payment->order_payment_expire); ?></span><span class="title"><?= TEXT_PAYM_METH_YEAR?></span></p>
                            </td>
                            <td class="bor_bot">
                              <p><?= _AUTOSHIP_PAYINFO_PAYAMOUNT?></p>
                              <p class="title"><?= $CURRENCY_DISPLAY->getFullValue($payment->order_payment_amount, 'TWN'); ?></p>
                          </td>
                          </tr>
                        <?php endif; ?>
                      </tr>
                      <?php endforeach ?>
                   </table>
                   <div class="t_align_r w_95">
                      <p class="title fon_size_9 p_top_2" ><?= TEXT_PAYM_NOTE?></p>
                   </div>
                   <h4 style="padding-left:0;font-size: 11.5px;" class="t_align_c"><?= SUB_TIT_PAYM_ONE?></h4>
                   <div class="w_95 center">
                      <p class="fon_size_9"><?= SUB_TIT_PAYM_ONE_TERMS?></p>
                   </div>
                     <div class="id_box">
                        <h2><?= TEXT_ID_FRONT?></h2>
                     </div>
                     <div class="id_box">
                        <h2><?= TEXT_ID_BACK?></h2>
                     </div>
                      <div>
                         <table align="left" style="width: 45%;line-height: 0px;border-collapse: collapse;margin-left: 15px;" class="fon_size_14">
                            <tr>
                               <td class="bor_bot">
                                  <p><?= TEXT_APPL_SIG?></p>
                                  <p class="title"></p>
                               </td>
                               <td class="nbsp"></td>
                               <td class="bor_bot">
                                  <p><?= TEXT_APPL_DATE?></p>
                                  <p class="title"></p>
                               </td>
                            </tr>
                         </table>
                         <table align="left" style="width: 45%;line-height: 0px;border-collapse: collapse;margin-left: 48px;" class="fon_size_14">
                            <tr>
                               <td class="bor_bot">
                                  <p><?= TEXT_LEG_REP_SIG?></p>
                                  <p class="title"></p>
                               </td>
                               <td class="nbsp"></td>
                               <td class="bor_bot">
                                  <p><?= TEXT_APPL_DATE?></p>
                                  <p class="title"></p>
                               </td>
                            </tr>
                         </table>
                      </div>
                   <h3 class="m_left_15 p_top_15"><?= SUB_TIT_PAYM_ONE?></h3>
                   <table align="center" style="width: 94%; line-height: 0px; border-collapse: collapse;" class="fon_size_14">
                      <tr class="t_align_l">
                         <td class="bor_bot">
                          <p><?= TEXT_FINAN_DEPART?></p><p><?= TEXT_FINAN_DEPART_IN_CHAR?></p>
                            <p class="title"></p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                          <p><?= TEXT_INFO_MANAG_DEP?></p><p><?= TEXT_INFO_MANAG_DEP_IN_CHAR?></p>
                            <p class="title"></p>
                         </td>
                         <td class="nbsp"></td>
                         <td class="bor_bot">
                          <p><?= TEXT_CUST_SERV_DEP?></p><p><?= TEXT_CUST_SERV_DEP_ADM_ASS?></p>
                            <p class="title"></p>
                         </td>
                      </tr>
                   </table>
                   <h3 class="m_left_15 "><?= TEXT_PERS_PIRACY_TIT?></h3>
                   <p class="fon_size_9 t_align_j w_95 center pad_0"><?= TEXT_PERS_PIRACY?></p>
                   <div class="w_95 center">
                      <p><?= TEXT_IBO_SIGN?>
                      <div class="bor_bot" style="width: 35%;margin-left: 170px;">
                      </div>
                      </p>

                    </div>
                    <p class="fon_siz_10" style="background: #CACACA;"><?= TEXT_CONTACT_ADD?></p>
                </div>
          <div>
          <div class="t_aligr_r">
            <p class="p_rigth_7"><?= TEXT_PAGE_2?></p>
          </div>
        <h3 class="t_align_c" style="background: #CACACA;border: 1px #CACACA solid;padding: 5px;color: black"><?= TITTLE_PAG_TWO?> </h3>
          <div>
          <div>
            <p class="fon_siz_11">
            <?= TEXT_CON_NEW_REG_TW?>
            </p>

          </div>

           <div class="w_95 center" style="display: table;">
           <div style="width: 70%;float: left;">
             <p><?= TEXT_CONFIRM_SIGN?></p>
           </div>
           <div style="width: 30%;float: left;">
             <p><?= TEXT_CONFIRM_SIGN_DATE?></p>
           </div>
          </div>
          <p class="fon_siz_10" style="background: #CACACA;"><?= TEXT_CONTACT_ADD?></p>


        </div>
        </body>
</html>
        <?
   }

?>
