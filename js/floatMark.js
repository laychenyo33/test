/*
Function : Floating block (using class)
Author : Audi
http://audi.tw
Date:March 2008
歡迎應用於無償用途散播，並請勿移除本版權宣告
*/

floatMark=function(layerName,scrollSpeed) {
	this.id=layerName;
	this.obj=document.getElementById(this.id);
	this.lastScrollY=0;
	this.scrollSpeed=scrollSpeed;	//捲動速度
}

floatMark.prototype.setScroll=function(time){
	var obj=this.obj;
	var lastScrollY=this.lastScrollY;
	var scrollSpeed=this.scrollSpeed;

	setInterval(function(){
		diffY = document.documentElement.scrollTop; //(document.all)?
		diffX = 0;


		//if (obj.style.visibility!='hidden'){
			if(diffY != lastScrollY){        
				percent = 1 * (diffY - lastScrollY) / scrollSpeed;
				if(percent > 0) percent = Math.ceil(percent);
				else percent = Math.floor(percent);

        	newY=getPosTop(obj);					
          newY+=percent;
					newY=newY+'px';
					
					obj.style.top = newY;
				/*if (document.all){
					newY=parseInt(obj.style.pixelTop);
					newY+=percent;
					newY=newY;
					obj.style.pixelTop = newY;
				}else{
					newY=parseInt(obj.style.top);
					newY+=percent;
					newY=newY+'px';
					obj.style.top = newY;
				}*/

				lastScrollY += percent;
			}
		//}
	},time);
}

//Not Use
floatMark.prototype.slide=function(){
	diffY = (document.all)?document.documentElement.scrollTop:self.pageYOffset;
	diffX = 0;

	window.status=diffY+','+this.obj.style.top;

	if (this.obj.style.visibility!='hidden'){
		if(diffY != this.lastScrollY){

			percent = 1 * (diffY - this.lastScrollY) / this.scrollSpeed;
			if(percent > 0) percent = Math.ceil(percent);
			else percent = Math.floor(percent);

			if (document.all){
				newY=parseInt(this.obj.style.pixelTop);
				newY+=percent;
				newY=newY;
				this.obj.style.pixelTop = newY;
			}else{
				newY=parseInt(this.obj.style.top);
				newY+=percent;
				newY=newY+'px';
				this.obj.style.top = newY;
			}

			this.lastScrollY += percent;
		}
	}
}

