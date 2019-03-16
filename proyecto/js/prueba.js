var xhr = new XMLHttpRequest();

function req (data,url) {
  	xhr.onreadystatechange = function(){
    	if( this.onreadyState == 4 && this.status == 200 ){
      	console.log(xhr.responseText);
    	}
  	};
  	xhr.open("POST",url,true);
  	xhr.send(data);
}