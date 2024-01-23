$(document).ready(function() {

  $("#logoff").click(function(){
    swal({
    title: "Logoff",
    text: "Are you sure?",
    icon: "warning",
    buttons: true,
    dangerMode: true,
    })
    .then((willDelete) => {
      if (willDelete) {
        window.location.href = `${base_url}/controllers/indexController.php?option=logoff`;
      } else {
        return false;
      }
    });
  });

  $("#home").click(function(){
		window.location.href = `${base_url}/views/packages.php`;
	});

  let page = $('#page').val();
  $('#selectedCtry').on('change', function() {
    window.location.href = "indexController.php?option=setCtry&ctry="+this.value+"&page="+page;
  });

  $('.langTab').click(function(){
    let lang=$(this).data("lang");
    window.location.href = "indexController.php?option=setLang&lang="+lang+"&page="+page;
  });

  $('.listName').click(function(){
    let id=$(this).data("id");
    window.location.href = "videoController.php?option=setTab&id="+id+"&page="+page;
  });

  $("#addLangTab").click(function(e){

		$('#newLang').find('option').remove().end().append('<option value="0" selected>Select</option>');
		let formData = new FormData();
		formData.append('option','allLangAv');
		$.ajax({
			url        : 'globalFunctions.php',
			type       : 'POST',
			data       : formData,
			cache      : false,
			contentType: false,
			processData: false,
		})
		.done(function(response) {
			if(response.success==='true'){
				$.each(response.info, function(k, v) {
					$("#newLang").append(`<option value="${v.lang_iso2}">${v.lang_iso2}</option>`);
				});
			  }else{
				$('#newLang').find('option').remove().end().append('<option value="0" selected>No languages available</option>');
			  }
		});
		$('#modalAddLang').modal('show');
	});

  $("#saveLang").click(function(e){
		let newLang = $('#newLang');
		if(newLang.val()=='0'){
			swal("Attention!", "Required fields", "warning");
      		return false;
		}
		let formData = new FormData();
		formData.append('lang',newLang.val());
		formData.append('option','addLang');
		$.ajax({
			url        : 'globalFunctions.php',
			type       : 'POST',
			data       : formData,
			cache      : false,
			contentType: false,
			processData: false,
			beforeSend : function() {
				showSwalGeneric();
				$('.swal-button-container').hide();
			}
		})
		.done(function(response) {
			$('#modalAddLang').modal('hide');
			swal('Success', response.info, "success");
			$('.swal-button-container').hide();
			setTimeout(function(){
				window.location.href = `${page}.php`;
			}, 1000);
		}).fail(function(e) {
			console.log("Something went wrong",e);
		});

	});

  const showSwalGeneric = () => {
		swal({
		  title            : "Processing...",
		  text             : "Please wait",
		  icon             : "img/ajax-loader.gif",
		  showConfirmButton: false,
		  allowOutsideClick: false
		});
	  }

	  let s3MaxLoadBytes = 0;
	  let s3MaxLoadDesc  = '';
	  //getMaxLoadBytes();
});

const showSwal = () => {
	swal({
	  title            : "Processing...",
	  text             : "Please wait",
	  icon             : `${base_url}/assets/img/ajax-loader.gif`,
	  showConfirmButton: false,
	  allowOutsideClick: false
	});
  }

	function validateSize(nameInput,labelInput,sizeBytes,MaxLoadBytes,s3MaxLoadDesc) {
		if(sizeBytes > MaxLoadBytes){
			let sizeFormat = formatBytes(sizeBytes,2);
			let $element = $(`#${nameInput}`);
			$element.wrap('<form>').closest('form').get(0).reset();
			$element.unwrap();
			$(`#${labelInput}`).html('');
			sizeAlert(sizeFormat,s3MaxLoadDesc);
			return false;
		}
		return true;
	}

	const sizeAlert = (sizeFormat,s3MaxLoadDesc) => {
		swal('Warning . . !', `Maximum file size:${s3MaxLoadDesc}, selected file size:${sizeFormat}`, "warning");
	}

	function formatBytes(bytes, decimals = 2) {
		if (bytes === 0) return '0 Bytes';
		const k = 1024;
		const dm = decimals < 0 ? 0 : decimals;
		const sizes = ['Bytes', 'K', 'M', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		const i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + '' + sizes[i];
	}
