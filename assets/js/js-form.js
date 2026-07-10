//angka menjadi format mata uang (1,000.00)
function FormatCurrency(objNum){	
	var num = objNum.value
	if(num==undefined)
		var num = objNum.val();
	var ent, dec;

	if (num != '' && num != objNum.oldvalue)
	{
		num = MoneyToNumber(num);
		if (isNaN(num))
		{		
			objNum.value = (objNum.oldvalue)?objNum.oldvalue:'';
		} else {
			var ev = (navigator.appName.indexOf('Netscape') != -1)?Event:event;
	
			if (ev.keyCode == 190 || !isNaN(num.split('.')[1]))
			{	
				objNum.value = AddCommas(num.split('.')[0])+'.'+num.split('.')[1];
			}
			else
			{	
				objNum.value = AddCommas(num.split('.')[0]);
			}
	
			objNum.oldvalue = objNum.value;
		}
	}
}

function numberWithCommas(num) {
    var parts = num.toString().split(".");
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    return parts.join(".");
}

function MoneyToNumber(num){
	if(num != '' && num != null)
	{
		// return num.split(',').join('');
		return num.toString().replace(/,/g, '');
	}
	else
	{
		return parseInt(0);
	}
}

function AddCommas(num){
	numArr=new String(num).split('').reverse();
	for (i=3;i<numArr.length;i+=3)
	{
		numArr[i]+=',';
	}
	return numArr.reverse().join('');
}

function NumberToMoney(num){
	numberArr = new String(num).split(',');
	numArr=new String(numberArr[0]).split('').reverse();
	for (i=3;i<numArr.length;i+=3)
	{
		numArr[i]+=',';
	}
	numberArr[0] = numArr.reverse().join('');
	if (numberArr[1] == null || numberArr[1] == '') numberArr[1] = '';
	return numberArr[0] + "" + numberArr[1];
}

function NumbToMonDot(num){
    numberArr = new String(num).split('.');
    numArr=new String(numberArr[0]).split('').reverse();
    for (i=3;i<numArr.length;i+=3)
    {
        numArr[i]+=',';
    }
    numberArr[0] = numArr.reverse().join('');
    if (numberArr[1] == null || numberArr[1] == '') numberArr[1] = '00';
    return numberArr[0] + "." + numberArr[1];
}

function NumbToMon(num){
    numberArr = new String(num).split('.');
    numArr=new String(numberArr[0]).split('').reverse();
    for (i=3;i<numArr.length;i+=3)
    {
        numArr[i]+='.';
    }
    numberArr[0] = numArr.reverse().join('');
    if (numberArr[1] == null || numberArr[1] == '') numberArr[1] = '00';
    return numberArr[0] + "," + numberArr[1];
}

function isPositive(number){
    if(number < 0){
        number = Math.abs(number);
        number = "(Rp. "+NumberToMoney(number)+")";
    }else{
        number = "Rp. "+NumberToMoney(number);
    }
    return number;
}

//inputan hanya angka
function onlyNumbers(e){
    var keynum;
    var keychar;

    if(window.event){  //IE
        keynum = e.keyCode;
    }
    if(e.which){ //Netscape/Firefox/Opera
        keynum = e.which;
    }
    if((keynum == 8 || keynum == 9 || keynum == 46 || (keynum >= 35 && keynum <= 40) ||
       (e.keyCode >= 96 && e.keyCode <= 105)))return true;

    if(keynum == 110 || keynum == 190){
        var checkdot=document.getElementById('price').value;
        var i=0;
        for(i=0;i<checkdot.length;i++){
            if(checkdot[i]=='.')return false;
        }
        if(checkdot.length==0)document.getElementById('price').value='0';
        return true;
    }
    keychar = String.fromCharCode(keynum);

    return !isNaN(keychar);
}

function formatNumber(num) {
	var n = num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,");
	n = n.split('.').join(','); 
    return n;
} 

//angka bulan menjadi huruf
function formatTanggal(tanggal){
	//19-04-2016
	var tgl = tanggal;
	
	var dd = tgl.substring(0,2);
	var mm = tgl.substring(3,5);
	var yy = tgl.substring(6);

	var strMonth = "";

	if(mm == '01'){
		strMonth = "Januari";
	}else if(mm == '02'){
		strMonth = "Februari";
	}else if(mm == '03'){
		strMonth = "Maret";
	}else if(mm == '04'){
		strMonth = "April";
	}else if(mm == '05'){
		strMonth = "Mei";
	}else if(mm == '06'){
		strMonth = "Juni";
	}else if(mm == '07'){
		strMonth = "Juli";
	}else if(mm == '08'){
		strMonth = "Agustus";
	}else if(mm == '09'){
		strMonth = "September";
	}else if(mm == '10'){
		strMonth = "Oktober";
	}else if(mm == '11'){
		strMonth = "November";
	}else if(mm == '12'){
		strMonth = "Desember";
	}

	return dd+" "+strMonth+" "+yy;
}

function formatBulan(bln){
	var strMonth = "";

	if(parseInt(bln) == 1){
		strMonth = "Januari";
	}else if(parseInt(bln) == 2){
		strMonth = "Februari";
	}else if(parseInt(bln) == 3){
		strMonth = "Maret";
	}else if(parseInt(bln) == 4){
		strMonth = "April";
	}else if(parseInt(bln) == 5){
		strMonth = "Mei";
	}else if(parseInt(bln) == 6){
		strMonth = "Juni";
	}else if(parseInt(bln) == 7){
		strMonth = "Juli";
	}else if(parseInt(bln) == 8){
		strMonth = "Agustus";
	}else if(parseInt(bln) == 9){
		strMonth = "September";
	}else if(parseInt(bln) == 10){
		strMonth = "Oktober";
	}else if(parseInt(bln) == 11){
		strMonth = "November";
	}else if(parseInt(bln) == 12){
		strMonth = "Desember";
	}

	return strMonth;
}

function shortMonth(tanggal){
	var strMonth = "";
	//19-04-2016
	var tgl = tanggal;
	
	var dd = tgl.substring(0,2);
	var bln = tgl.substring(3,5);
	var yy = tgl.substring(6);

	if(parseInt(bln) == 1){
		strMonth = "Jan";
	}else if(parseInt(bln) == 2){
		strMonth = "Feb";
	}else if(parseInt(bln) == 3){
		strMonth = "Mar";
	}else if(parseInt(bln) == 4){
		strMonth = "Apr";
	}else if(parseInt(bln) == 5){
		strMonth = "Mei";
	}else if(parseInt(bln) == 6){
		strMonth = "Jun";
	}else if(parseInt(bln) == 7){
		strMonth = "Jul";
	}else if(parseInt(bln) == 8){
		strMonth = "Agt";
	}else if(parseInt(bln) == 9){
		strMonth = "Sep";
	}else if(parseInt(bln) == 10){
		strMonth = "Okt";
	}else if(parseInt(bln) == 11){
		strMonth = "Nov";
	}else if(parseInt(bln) == 12){
		strMonth = "Des";
	}

	return dd+" "+strMonth+" "+yy;
}

function stringToDate(_date,_format,_delimiter){
    var formatLowerCase=_format.toLowerCase();
    var formatItems=formatLowerCase.split(_delimiter);
    var dateItems=_date.split(_delimiter);
    var monthIndex=formatItems.indexOf("mm");
    var dayIndex=formatItems.indexOf("dd");
    var yearIndex=formatItems.indexOf("yyyy");
    var month=parseInt(dateItems[monthIndex]);
    month-=1;
    var formatedDate = new Date(dateItems[yearIndex],month,dateItems[dayIndex]);
    return formatedDate;
}

function angkaRomawi(num) {
	var lookup = {M:1000,CM:900,D:500,CD:400,C:100,XC:90,L:50,XL:40,X:10,IX:9,V:5,IV:4,I:1},
	  roman = '',
	  i;
	for ( i in lookup ) {
	while ( num >= lookup[i] ) {
	  roman += i;
	  num -= lookup[i];
	}
	}
	return roman;
}