import { Component } from '@angular/core';
import { HttpClient } from '@angular/common/http';

@Component({
	selector: 'app-root',
	templateUrl: './app.component.html',
	styleUrls: ['./app.component.css']
})
export class AppComponent {
	title = 'Iformbuilder APP';
	access_token = '';
	user:object = {};
	constructor(private http: HttpClient) {
		console.log('Hello RestServiceProvider Provider');
	}

	getAccessToken = function(){
		this.http.get('http://fouraxiz.com/iformbuilder/index.php').subscribe(result => {
			this.access_token = result.token;
		});
	}

	saveData = function(user){
		if(user.username==''){
			alert('Please enter username');
			return false;
		}
		if(user.email==''){
			alert('Please enter vaild email');
			return false;
		}
		var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        
		if(!regex.test(user.email)){
			alert('Please enter vaild email');
			return false;
		}
		if(user.phone_number==''){
			alert('Please enter vaild phone number');
			return false;
		}
		if(user.birthdate==''){
			alert('Please enter birthdate');
			return false;
		}
		if(user.profession==''){
			alert('Please select profession');
			return false;
		}
		user.phone_number = user.phone_number.replace(/(\d\d\d)(\d\d\d)(\d\d\d\d)/, "($1) $2-$3");
		this.http.post('http://fouraxiz.com/iformbuilder/index.php',user,{
    headers : {
        'Content-Type' : 'application/x-www-form-urlencoded; charset=UTF-8'
    }}).subscribe(result => {
    	console.log(result);
		if(result.msg=='success'){
			alert('Data Saved Successfully');
			window.location.reload();
		}else{
			alert(result.msg.error_message);
		}
		
		},error=>{
			console.log(error);
		});
	}

phonenumberValidation = function(e) {
	var phoneNumberString = e.target.value;
	if(phoneNumberString.length<10){
		alert('Please enter minimum 10 digit phone number');
		return false;
	}
    var cleaned = ('' + phoneNumberString).replace(/\D/g, '');

    e.target.value = cleaned.replace(/(\d\d\d)(\d\d\d)(\d\d\d\d)/, "($1) $2-$3");

  }
numberOnly = function (event) {
   const pattern = /[0-9\ ]/;

    let inputChar = String.fromCharCode(event.charCode);
    if (event.keyCode != 8 && !pattern.test(inputChar)) {
      event.preventDefault();
    }

  }

 validateEmail = function(email) {
        var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        if(!regex.test(email)) {
           alert('Please enter a valid email address');
           return false;
        }else{
           return true;
        }
      }

}


