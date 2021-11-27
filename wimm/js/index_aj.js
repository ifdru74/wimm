/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var bCtrlHit = false;   // ctrl held down
var aParts = [[0,4,-1,0], [5,2,12,1], [8,2,31,1], [11,2,23,0], [14,2,59,0], [17,2,59,0]];   // text parts
var aDays = [0,31,28,31,30,31,30,31,31,30,31,30,31];    // month's days
var jqxr = null;    // jQuery ajax request
const cEmptyString = '';
const cSuccess = 'success';
// binding parameters
const cRowBindType = 'bind_row_type';
const cRowBindID   = 'bind_row_id';
// bind types
const cBindTitle = 'title';
const cBindValue = 'value';
const cBindLabel = 'label';
const cAutoCItem = 'aci_';
// form POST modes
const cModeRefresh = 'refresh';
const cModeInsert  = 'insert';
const cModeDelete  = 'delete';
// jquery element selectors
const cFormMode  = '#FRM_MODE';
const cSelRowID  = '#sel_row_id';
const cFormSelID = '#expenses';
const cDialogBox = '#dialog_box';
const cDialogBoxV= '#dialog_box:visible';
const cFormID    = 'expenses';
// form element selectors
const cFormField    = '.form_field';
const cFormSendable = '.sendable';
// dialog actions
const cFormHide = 'hide';

/**
 * converts datetime string to display
 * @param {String} sDate
 * @returns {String}
 */
function format_date(sDate)
{
    var sRet;
    try
    {
        var d = new Date();
        d.parse(sDate);
        sRet = d.toISOString();
    }
    catch(e)
    {
        sRet = sDate.trim();
    }
    if(sRet!==undefined && sRet!==null && sRet.length>18)    {
        // 2014-06-01 08:05:50 -> 01/06 08:05:50
        sRet = sRet.substr(8, 2) + "/" + sRet.substr(5, 2) + " " +
                sRet.substr(11, 8);
    }
    return sRet;
}
/**
 * 
 * @param {object} s_msg
 * @returns nothing
 */
function console_debug_log(s_msg)
{
    if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1 ||
            navigator.userAgent.toLowerCase().indexOf('chrome') > -1)   {
        console.log(s_msg);
    }
}

/**
 * cancel all queries and close dialog
 * @returns nothing
 */
function doCancel()
{
    if(jqxr!==null) {
        jqxr.abort();
        jqxr = null;
    }
    $(cDialogBox).hide();
    $(cSelRowID).val(cEmptyString);
    $(cFormMode).val(cModeRefresh);
}

/**
 * finishes AJAX request
 * @param {object} jqXHR - query
 * @param {String} textStatus - status
 * @returns nothing
 */
function onTxComplete(jqXHR, textStatus )
{
    if(textStatus.indexOf(cSuccess)===0)    {
        var row_id = $("#HIDDEN_ID").val();
        if(row_id!==null&&row_id!==undefined&&row_id.length>0)  {
            var i, v;
            var s1 = '{"id":"1","err":"error"}';
            console.debug('response=' + jqXHR.responseText);
            if(jqXHR.responseText!==null && 
                    jqXHR.responseText!==undefined && 
                    jqXHR.responseText.length>0)  {
                i = JSON.parse(jqXHR.responseText);
                console.debug("id="+i.id);
                console.debug("error="+i.err);
                var v_sum = Number(i.t_sum);
                console.debug("sum="+v_sum);
                v = $(cFormMode).val();
                console.debug("mode="+v);
                if(v.indexOf(cModeDelete)===0)
                {
                    $("#"+row_id).parent().parent().remove();
                }
                else {
                    $("#T_SUMM_"+row_id).text(v_sum.toFixed(2));
                    var form_fields = $(cFormSelID).find(cFormField);
                    for(i=0; i<form_fields.length; i++)
                    {
                        var bt = form_fields[i].getAttribute(cRowBindType);
                        var bi = "#" + form_fields[i].getAttribute(cRowBindID) + row_id;
                        var v = form_fields[i].value;
                        if(v.substr(0,4)===cAutoCItem)
                        {
                            v = v.substr(4);
                        }
                        if(form_fields[i].id.indexOf('t_date')===0)
                        {
                            $(bi).attr(cBindTitle, v);
                            $(bi).text(format_date(v));
                        }
                        else
                        {
                            switch(bt)
                            {
                            case cBindValue:
                                $(bi).val(v);
                                break;
                            case cBindLabel:
                                $(bi).text(v);
                                break;
                            case cBindTitle:
                                $(bi).attr(cBindTitle, v);
                                break;
                            }
                        }
                    }
                }
            }
            else
                alert("Пустой ответ!");
        }
        else
        {
            console.debug("new row.");
            console.debug('response=' + jqXHR.responseText);
            v = $(cFormMode).val();
            console.debug("mode="+v);
            if(v.indexOf(cModeInsert)===0)
            {
                if(jqXHR.responseText!==null && jqXHR.responseText!==undefined && 
                        jqXHR.responseText.length>0)  {
                    i = JSON.parse(jqXHR.responseText);
                    console.debug("id="+i.id);
                    console.debug("error="+i.err);
                }                
            }
        }
    }
    else {
        console.debug(textStatus);
    }

    jqxr = null;
    $(cDialogBox).modal(cFormHide);
}

/**
 * submit AJAX request
 * @param {String} submitURL
 * @returns nothing
 */
function tx_submit(submitURL)
{
    var i;
    var f,v;
    var inv_idx = -1;
    var msg = cEmptyString;
//    var currentdate = new Date(); 
    v = $(cFormMode).val();
    if(v===cModeInsert)
    {
        $(cFormSelID).submit();
        return;
    }
    var reqStr = "time=" + Date.now() + "&FRM_MODE=" + v + 
            "&HIDDEN_ID=" + $("#HIDDEN_ID").val();
    var fields = $(cFormSelID).find('.sendable');
    if(fields!==null && fields!==undefined)
    {
        for(i=0; i<fields.length; i++)
        {
            try {
                f = fields[i].getAttribute("id");;
                v = fields[i].value;
                if(v.substr(0,4)===cAutoCItem)
                {
                    v = v.substr(4);
                }
                reqStr += ("&" + f.toString() + "=" + encodeURIComponent(v));
            } catch (e) {
                inv_idx = i;
                msg = e.toString();
            }
        }
    }
    console.debug("request:"+reqStr);
    jqxr = $.ajax({
        type: "POST",
        url: submitURL,
        data: reqStr,
        complete: onTxComplete
    });
    console.debug("end query!");
}

/**
 * process key pressed
 * @param {Number} key
 * @returns nothing
 */
function onPageKey(key)
{
    switch(key)
    {
        case 27:
            if($(cDialogBoxV).length > 0) {
                doCancel2();
                $(cDialogBox).modal(cFormHide);
            }
            break;
        case 10:
            if($(cDialogBoxV).length > 0) {
                if(fancy_form_validate(cFormID)) 
                {
                    tx_submit('/wimm2/wimm_edit2.php');
                }
            }
            break;
        case 116:
            $(cSelRowID).val(cEmptyString);
            $(cFormMode).val(cModeRefresh);
            $(cFormSelID).submit();
           break;
    }

}

/**
 * set selection range in text box
 * @param {Object} el - DOM element
 * @param {Number} nStart - start selection
 * @param {Number} nEnd - end of selection
 * @returns {undefined}
*/
function setInputSelection(el, nStart, nEnd) 
{
    var start = 0, end = 0, normalizedValue, range,
        textInputRange, len, endRange;

    try
    {
        if (typeof el.selectionStart === "number" && typeof el.selectionEnd === "number") {
            el.selectionStart = nStart;
            el.selectionEnd = nEnd;
        } 
    }
    catch(e)
    {
        console.debug("set date & time selection: " + e.toString());
    }
}

/**
 * get selection range from text box
 * @param {Object} el - DOM element
 * @returns {getInputSelection.index_ajAnonym$2}
 */
function getInputSelection(el) {
    var start = 0, end = 0, normalizedValue, range,
        textInputRange, len, endRange;

    try
    {
        if (typeof el.selectionStart === "number" && typeof el.selectionEnd === "number") {
            start = el.selectionStart;
            end = el.selectionEnd;
        }
        return {
            start: start,
            end: end
        };
    }
    catch(e)
    {
        console.debug("get date & time selection: " + e.toString());
    }
    return null;
}

/**
 * pad string from left to a desired legth
 * @param {String} i
 * @param {Number} l
 * @param {String} f
 * @returns {String} - padded string 
 */
function formatNumber(i, l, f)
{
    var s1 = i.toString();
    var len = s1.length;
    var sRet = cEmptyString;
    if(len<l)   {
        for(i=0; i<l-len; i++)
            sRet += f.substring(0,1);
        sRet += s1;
    }
    else {
        if(len>l)   {
            sRet = s1.substring(0,l);
        }
        else
            sRet = s1;
    }
    return sRet;
}

/**
 * set event handlers with specified jQuery selector
 * @param {String} jqSelector
 * @returns {undefined}
 */
function setHandlers(jqSelector)
{
    bCtrlHit = false;
    $(jqSelector).focus(function(e)
    {
        setInputSelection(e.currentTarget, aParts[0][0], 
            aParts[0][0] + aParts[0][1]);
        e.preventDefault();
    });
    $(jqSelector).keydown(function(e)
    {
        switch(e.keyCode)
        {
            case 17:
                bCtrlHit = true;
                break;
            case 38://up
            case 40://down
                e.preventDefault();
                break;
        }
    });
    $(jqSelector).keyup(function(e)
    {
        var pos, len, val, i;
        var caret = getInputSelection(e.currentTarget);
        if(caret!==null)
        {
            var month;
            val = $(this).val();
            len = val.length;
            pos = caret.start;
            month = new Number(val.substring(aParts[1][0],aParts[1][0]+aParts[1][1])).valueOf();
            switch(e.keyCode)
            {
                case 17:
                    bCtrlHit = false;
                    break;
                case 37://left
                    if(bCtrlHit)    {
                        if(pos>=len)
                            break;
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
                                if(i<1)
                                    break;
                                setInputSelection(e.currentTarget, aParts[i-1][0], 
                                    aParts[i-1][0] + aParts[i-1][1]);
                                break;
                            }
                        }
                    }
                    break;
                case 39://right
                    if(bCtrlHit)    {
                        if(pos<=0)
                            break;
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
    //                                        if(i<1)
    //                                            break;
                                setInputSelection(e.currentTarget, aParts[i][0], 
                                    aParts[i][0] + aParts[i][1]);
                                break;
                            }
                        }
                    }
                    break;
                case 38://up
                    if(!bCtrlHit)    {
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
                                var n1 = val.substring(aParts[i][0], aParts[i][0] + aParts[i][1]).valueOf();
                                n1 ++;
                                if(aParts[i][2]>=0)  {
                                    var nLim;
                                    if(i===2)
                                        nLim = aDays[month];
                                    else
                                        nLim = aParts[i][2];
                                    if(n1>nLim)
                                        n1 = aParts[i][3];
                                }
                                $(this).val(val.substring(0,aParts[i][0]) +
                                        formatNumber(n1.toString(),aParts[i][1],"0")+
                                        val.substring(aParts[i][0]+aParts[i][1]));
                                setInputSelection(e.currentTarget, aParts[i][0], 
                                    aParts[i][0] + aParts[i][1]);
                                e.preventDefault();
                                break;
                            }
                        }
                    }
                    break;
                case 40://down
                    if(!bCtrlHit)    {
                        for(i=0; i<aParts.length; i++)
                        {
                            if(aParts[i][0]>=pos)    {
                                var n1 = val.substring(aParts[i][0], aParts[i][0] + aParts[i][1]).valueOf();
                                n1 --;
                                if(aParts[i][3]>=0)  {
                                    if(n1<aParts[i][3]) {
                                        var nLim;
                                        if(i===2)
                                            nLim = aDays[month];
                                        else
                                            nLim = aParts[i][2];
                                        if(nLim>0)
                                            n1 = nLim;
                                        else
                                            n1 = aParts[i][3];
                                    }
                                }
                                $(this).val(val.substring(0,aParts[i][0]) +
                                        formatNumber(n1.toString(),aParts[i][1],"0")+
                                        val.substring(aParts[i][0]+aParts[i][1]));
                                setInputSelection(e.currentTarget, aParts[i][0], 
                                    aParts[i][0] + aParts[i][1]);
                                e.preventDefault();
                                break;
                            }
                        }
                    }
                    break;
            }
            //e.preventDefault();
        }
    });
}
