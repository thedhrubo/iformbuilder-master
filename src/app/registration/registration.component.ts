import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
@Component({
  selector: 'app-registration',
  templateUrl: './registration.component.html',
  styleUrls: ['./registration.component.css']
})
export class RegistrationComponent implements OnInit {

  ngOnInit() {
  	this.getData();
  }
title = 'Iformbuilder APP';
	access_token = '';
	user:object = {};
	dataList:object = {};
	username = '';
	enableEdit = false;
	constructor(private http: HttpClient) {
		
	}

getData = function(){
		this.http.get('http://fouraxiz.com/iformbuilder/index.php/iformbuilder_api/getData?rnd='+new Date().getTime())
		.subscribe(result => {
    	console.log(result);
    	this.dataList = result;
		},error=>{
			console.log(error);
		});
	}




	getAccessToken = function(){
		this.http.get('http://fouraxiz.com/iformbuilder/index.php').subscribe(result => {
			this.access_token = result.token;
		});
	}
    editData = function(row){
       this.user.first_name = row.first_name;
	   this.user.last_name = row.last_name;
	   this.user.username = row.username;
	   this.user.phone_number = row.phone_number;
	   this.user.email = row.email;
	   this.user.birth_date = row.birth_date;
	   this.user.address = row.address;
	   this.user.profession = row.profession;
	   this.user.subscribe = row.subscribe;
	   this.user.id = row.id;
        this.enableEdit = true;
    }
	   showTable = function(row){
        this.enableEdit = false;
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
		console.log(user);
		this.http.post('http://fouraxiz.com/iformbuilder/index.php/iformbuilder_api/updateData',user,{
    headers : {
        'Content-Type' : 'application/x-www-form-urlencoded; charset=UTF-8'
    }}).subscribe(result => {
    	console.log(result);
		if(result.msg=='success'){
			alert('Data Saved Successfully');
			window.location.href = '/';
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
