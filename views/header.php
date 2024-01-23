<meta charset = "utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo PAGE_TITLE; ?></title>
<link rel="icon" href="<?php echo BASE_URL;?>/assets/img/favicon.ico" />
<!--  Bootstrap v3.3.2  -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/solid.css" integrity="sha384-Tv5i09RULyHKMwX0E8wJUqSOaXlyu3SQxORObAI08iUwIalMmN5L6AvlPX2LMoSE" crossorigin="anonymous"/>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/fontawesome.css" integrity="sha384-jLKHWM3JRmfMU0A5x5AkjWkw/EYfGUAGagvnfryNV3F9VqM98XiIH7VBGVoxVSc7" crossorigin="anonymous"/>

<link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<link href="<?php echo BASE_URL;?>/assets/css/styles.css" rel="stylesheet">

<script src="<?php echo BASE_URL;?>/assets/js/sweetalert.min.js"></script>
<script>
    let base_url = '<?php echo BASE_URL;?>';
    let s3MaxLoadBytes = <?php echo MAX_LOAD_BYTES;?>;
    let s3MaxLoadDesc  = '<?php echo MAX_LOAD_DESC;?>';
    let codEnzB64      = '<?php echo COD_ENZ_B64;?>';
    let folder_qr      = '<?php echo FOLDER_QR;?>';
</script>