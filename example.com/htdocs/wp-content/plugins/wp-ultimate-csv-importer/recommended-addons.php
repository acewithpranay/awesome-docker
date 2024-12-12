<?php
/**
 * WP Ultimate CSV Importer plugin file.
 *
 * Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 */


if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


?>
<?php
  $img_Url=WP_PLUGIN_URL . '/wp-ultimate-csv-importer/assets/images/pro.png';
?>
  <h3 class="manage_addon_main_page"><img src="<?php echo $img_Url; ?>" width="60px" alt="">Ultimate CSV Importer Free</h3>
  <div class="settings dark">
   
  <?php  
      
      $gif = WP_PLUGIN_URL . '/wp-ultimate-csv-importer/assets/images/ajax-loader.gif';
 
  ?>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
    
    <div class="row">
    <section class="music">
   
 
   
     <h1>Manage Addons</h1>
     
     
   
   <div class="import_1">
     <h2>Import Users</h2>
       
        <div class="row">
          <div class="col-8">

    
          
        <p><?php echo esc_html__('Import your user records available in the CSV/XML file with custom fields, Woocommerce Shipping and Billing details.','wp-ultimate-csv-importer');?></p>
        </div>
        <div class="col-4">
        <?php if(is_plugin_active('import-users/import-users.php')){
                  print'<button name="get" data-value="Users" id="btn_install_act" disabled="disabled">Activated</button>';}
             else{print '<button class = "btn_install" name="get" value="Users" id="Useraddon">Install & Activate</button>';}
       ?>
        </div>

      </div>  
   </div>
   <div class="import_2">
     <h2>Import Woocommerce</h2>
       
        <div class="row">
          <div class="col-8">

          <p><?php echo esc_html__('Import your WooCommerce Products records with attributes, categories, tags, and images available in the CSV/XML file.','wp-ultimate-csv-importer');?></p>
        </div>
        <div class="col-4">
        <?php if(is_plugin_active('import-woocommerce/import-woocommerce.php')){
                print '<button name="get"data-value="WooCommerce" id="btn_install_act" disabled="disabled">Activated</button>';}
             else{print '<button class = "btn_install" name="get" value="WooCommerce" id="WooCommerceaddon">Install & Activate</button>';}?>
        </div>

      </div>  
   </div>
   <div class="import_3">
     <h2>Export Wordpress Data</h2>
       
        <div class="row">
          <div class="col-8">

    
          
          <p><?php echo esc_html__('Export your Posts, Pages, Custom Posts, Users, Comments, and WooCommerce Products data as CSV files from the WordPress.','wp-ultimate-csv-importer');?>
      </p>
        </div>
        <div class="col-4">
        <?php if(is_plugin_active('wp-ultimate-exporter/wp-ultimate-exporter.php')){
                 print '<button name="get"data-value="Exporter" id="btn_install_act" class="btn_g" disabled="disabled" >Activated</button>';}
              else{print '<button class = "btn_install" name="get" value="Exporter" id="Exporteraddon" class="btn_g">Install & Activate</button>';}?>
        </div>

      </div>  
   </div>

   <div input type="button" class="buttonmid" id="click_get_started">
        <?php echo esc_html__('Install & Activate All','wp-ultimate-csv-importer');?>
         
        </div>
      
    
      </div>
 </section>

        
  </div>
  <div class="setting_back">
    <a href='admin.php?page=com.smackcoders.csvimporternew.menu' ><button class="btn_install">Back to Our Plugin</button></a>
  </div>

<style>
  
  .manage_addon_main_page {
  text-align: center;
  margin-top: 20px;
}
*, *:before, *:after {
  box-sizing: border-box;
}
#click_get_started{

  margin-top:50px;
}
.import_3 {
    margin-left: 33px;
}
.btn_g{
  margin-right:28px;
}
#btn_install_act{
    color: #fff;
    border:none;
    background: #00a699;
    opacity:0.65;
    vertical-align: top;
    width:130px;
    height:33px;
    font-size:15px;
    align-self: end;
    border-radius:7px;
    font-weight: bold;
    cursor:pointer;
    
}
#wpbody-content{
  background-color:#f3f5f8;
}
.btn_install{
    color: #fff;
    border:none;
    background: #00a699;
    vertical-align: top;
    width:150px;
    height:33px;
    font-size:15px;
    align-self: end;
    border-radius:7px;
    font-weight: bold;
    cursor:pointer;
    
}
/* #btn_install:hover{
  width:25%;
  transition-delay: .2s;
} */



.import_1{

    display: flex;
    flex-flow: column;
    margin-left:45px;
    margin-top:25px;
}
.import_2{

display: flex;
flex-flow: column;
margin-top:50px;
margin-left:45px;
}
.import_3{

display: flex;
flex-flow: column;
margin-left:45px;
margin-top:50px;
}


.music a {
  text-decoration: none;
  font-size: 17px;
    color: #00a699;
    
    font-weight: bold;
}

.container {
  display: flex;
  justify-content: center;
  margin: 10% 5%; 
}

.logo{
   display:flex;
}
.p_text{
  font-size:20px;
  text-align:center;
  
}
.settings {
  width: 650px;
  height: 520px;

  padding: 5px 15px;
  border-radius:10px;
  
}
.setting_back{
  width: 650px;
  display: flex;
  justify-content: flex-end;
  margin: auto;
  margin-top: 30px;
}
.setting_back .btn_install{
  height: 40px;
  width: 160px;
  
}

.row {
  display: -ms-flexbox;
  display: flex;
  -ms-flex-wrap: wrap;
  flex-wrap: wrap;
}
p {
    color: #00a699;
    /* text-align:center; */
}
header {
  display: inline-flex;
  width: 100%;
  margin: 25px 0 15px;
  justify-content: space-between;
}


section h1 {
  font-size: 36px;
 
    color: #00a699;
    margin-top: 32px;
    
    max-width: 84%;
}

section.music{
  justify-content:center;

}

.buttonmid{
  
  width: 166px;
    height: 39px;
    margin: 0 auto 0 auto;
    margin-top: 18px;
    background: #00a699;
    text-align: center;
    cursor: pointer;
    -moz-transition: all 0.4s ease-in-out;
    -o-transition: all 0.4s ease-in-out;
    -ms-transition: all 0.4s ease-in-out;
    transition: all 0.4s ease-in-out;
    color: #fff;
    font-size: 15px;
    font-weight: bold;
    text-decoration: none;
    line-height: 2.5;
    border-radius: 7px;
    text-decoration: none;
}
.buttonmid:hover{
  width:40%;
}

header .profile {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #666886;
}

.profile img {
  width: 100%;
  border-radius: 50%;
}

section {
  display: flex;
  flex-flow: row wrap;
  width: 100%;
}

section h2 {
  font-size: 23px;
  color: #00a699;
  
  margin: 13px 0px;
}

section p {
  font-size: 12px;
  margin-top: 0px;
  margin-bottom: 0px;
  letter-spacing: 0.05rem;
  color:#000000;
  /* font-family:'Times New Roman', Times, serif; */

}
section span  {
  margin-top: 10px;
  
}

.music {
  justify-content: space-between;
}

.music .quality {
    cursor: pointer;
}



.music span{
  text-align: right;

}

.music > .slider {
  display: block;
  width: 2.5rem;
  height: 1rem;
  position: relative;
  margin-right: 10px;
 
}

.music > .slider input {
  opacity: 0;
  
}

.music > .slider label {
  content: 'off';
    position: absolute;
    background-color: rgb(241, 241, 241);
    width: 39px;
    height: 17px;
    margin-top: 10px;
    top: 0;
    left: 0;
    border-radius: 1.5rem;
    -webkit-transition: background-color .2s ease-in-out;
    transition: background-color .2s ease-in-out;
}


.music > .slider label:after {
  content: '';
    position: absolute;
    display: block;
    width: 15px;
    height: 15px;
    border-radius: 1.5rem;
    margin-top: 1px;
    cursor: pointer;
    top: 0;
    z-index: 1;
    left: .15rem;
    background-color: #00a699;
    -webkit-transition: left .2s ease-in-out;
    transition: left .2s ease-in-out;
}

.music > .slider input[type=checkbox]:checked ~ label {
  background-color: rgb(0,225,225);
}
.music > .slider input[type=checkbox]:checked ~ label:after{
  left: 1.5rem;
}

#theme {
  cursor: pointer;
}





/* dark theme */
.dark {
  background-color: #ffffff;
  color: #e1e1e1;
  margin: 0 auto;
  margin-top: 20px;
  height:625px;
  box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;


}

.dark h2, .dark span {
  color: f3e8e8;
}

.dark .user input {
  background-color: rgba(0,0,0,.2);
  color: #e1e1e1;
}
.dark .user input:focus {
  outline: -webkit-focus-ring-color auto 2px;
  outline-color: rgb(0,252,252);
}


/* light theme */
.light {
  color: #1a1f2b;
  background-color: #e1e1e1;
}

.light .social > .sm label:after {
  color: #a1a1a1;
}


  
</style>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
