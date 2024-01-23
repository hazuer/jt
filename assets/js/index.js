$(document).ready(function() {
	let btn_login = $('#btn-login');
	let username  = $('#username');
	let password  = $('#password');

	btn_login.click(function(){

		if(username.val()=='' || password.val()==''){
			swal("Attention!", "Required fields (*)", "warning");
			return false;
		}

		let formData = new FormData();
		formData.append('username',username.val());
		formData.append('password',password.val());
		formData.append('option','login');
		$.ajax( {
		url        : `${base_url}/controllers/indexController.php`,
		type       : 'POST',
		data       : formData,
		cache      : false,
		contentType: false,
		processData: false,
		beforeSend : function() {
				showSwal();
				$('.swal-button-container').hide();
			}
		})
		.done(function(response) {
			setTimeout(function(){
				swal.close();
				if(response.success==='true'){
					window.location.href = `${base_url}/views/packages.php`;
				}else{
					swal("Error!", "Invalid username or password", "warning");
				return false;
				}
			}, 1000);
		}).fail(function(e) {
			console.log("Something went wrong",e);
		})
	});

});