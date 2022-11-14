//기준월 첫날
function getMonthFirst(dt){
    var newDt = new Date(dt);
    newDt.setDate(1);
    return converDateString(newDt);
}
//기준월 말일
function getMonthLast(dt){
    var newDt = new Date(dt);
    newDt.setMonth( newDt.getMonth() + 1);
    newDt.setDate(0);
    return converDateString(newDt);
}
//이전달 첫날
function getPrevMonthFirst(dt){
    var newDt = new Date(dt);
    newDt.setMonth( newDt.getMonth() - 1 );
    newDt.setDate( 1);
    return converDateString(newDt);
}
//이전달 말일
function getPrevMonthLast(dt){
    var newDt = new Date(dt);
    newDt.setMonth( newDt.getMonth() );
    newDt.setDate(0);
    return converDateString(newDt);
}
//다음달 첫날
function getNextMonthFirst(dt){
    var newDt = new Date(dt);
    newDt.setMonth( newDt.getMonth() + 1 );
    newDt.setDate( 1);
    return converDateString(newDt);
}
//다음달 말일
function getNextMonthLast(dt){
    var newDt = new Date(dt);
    newDt.setMonth( newDt.getMonth() + 2 );
    newDt.setDate(0);
    return converDateString(newDt);
}
//몇달 후 말일
function getNthNextMonthLast(s, i){
    var newDt = new Date(s);
    newDt.setMonth( newDt.getMonth() + i );
    newDt.setDate(0);
    return converDateString(newDt);
}
//몇달 후 첫날
function getNthNextMonthFirst(s, i){
    var newDt = new Date(s);
    newDt.setMonth( newDt.getMonth() + i );
    newDt.setDate(1);
    return converDateString(newDt);
}
//몇일 전
function getNthPrevDay(s, i){
    var newDt = new Date(s);
    newDt.setDate( newDt.getDate() - i );
    return converDateString(newDt);
}
//몇일 후
function getNthNextDay(s, i){
    var newDt = new Date(s);
    newDt.setDate( newDt.getDate() + i );
    return converDateString(newDt);
}

function converDateString(dt){
    return dt.getFullYear() + "-" + addZero(eval(dt.getMonth()+1)) + "-" + addZero(dt.getDate());
}

function addZero(i){
    var rtn = i + 100;
    return rtn.toString().substring(1,3);
}

function addDate(sDate,nNum){
    var yy = parseInt(sDate.substr(0, 4), 10);
    var mm = parseInt(sDate.substr(5, 2), 10);
    var dd = parseInt(sDate.substr(8, 2), 10);

    d = new Date(yy, mm - 1, dd + nNum);//날수를 더할경우

    yy = d.getFullYear();
    mm = d.getMonth() + 1; mm = (mm < 10) ? '0' + mm : mm;
    dd = d.getDate(); dd = (dd < 10) ? '0' + dd : dd;

    return yy + '-' +  mm  + '-' + dd;   
}
