/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
var jqxr = null;
// type - value type "s" - text, "n" - numeric, "d" - date
// tbl_type - table disp type "t" - text, "v" - value, i - title
var a_fields = [
    {id:"t_user",len:0,type:"n",val:0,in_tbl:"#L_USR_",tbl_type:"t"},
    {id:"t_user",len:0,type:"n",val:0,in_tbl:"#T_USR_",tbl_type:"v"},
    {id:"t_name",len:0,type:"s",val:0,in_tbl:"#TNAME_",tbl_type:"t"},
    {id:"t_type",len:0,type:"n",val:0,in_tbl:"#T_TYPE_",tbl_type:"v"},
    {id:"t_curr",len:0,type:"n",val:0,in_tbl:"#T_CURR_",tbl_type:"v"},
    {id:"t_sum",len:0,type:"n",val:0,in_tbl:"#T_SUMM_",tbl_type:"t"},
    {id:"t_date",len:0,type:"d",val:0,in_tbl:"#T_DATE_",tbl_type:"t"},//2014-05-05 05:05:05
    {id:"t_date",len:18,type:"d",val:0,in_tbl:"#T_DATE_",tbl_type:"i"},//2014-05-05 05:05:05
    {id:"t_place",len:0,type:"n",val:0,in_tbl:"#T_PLACE_",tbl_type:"v"},
    {id:"t_budget",len:0,type:"n",val:0,in_tbl:"#T_BUDG_",tbl_type:"v"}];

/**
 * 
 * @param {object} s_msg
 * @returns {undefined}
 */
function console_debug_log(s_msg)
{
    if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1)   {
        console.log(s_msg);
    }
}

function doCancel()
{
    if(jqxr!==null) {
        jqxr.abort();
        jqxr = null;
    }
    $("#dialog_box").hide();
    $("#sel_row_id").val("");
    $("#FRM_MODE").val("refresh");
}

function onTxComplete(jqXHR, textStatus )
{
    if(textStatus.indexOf("success")==0)    {
        var row_id = $("#HIDDEN_ID").val();
        if(row_id!==null&&row_id!==undefined&&row_id.length>0)  {
            var i, v;
            var s1 = '{"id":"1","err":"error"}';
            console_debug_log('response=' + jqXHR.responseText);
            if(jqXHR.responseText!==null && jqXHR.responseText!==undefined && 
                    jqXHR.responseText.length>0)  {
                i = JSON.parse(jqXHR.responseText);
                console_debug_log("id="+i.id);
                console_debug_log("error="+i.err);
                v = $("#FRM_MODE").val();
                console_debug_log("mode="+v);
                if(v.indexOf("delete")==0)  {
                    $("#TR_"+row_id).remove();
                }
                else {
                    for(i=0; i<a_fields.length; i++)    {
                        v = $("#"+a_fields[i].id).val();
                        if(a_fields[i].tbl_type.indexOf("v"))
                            $(a_fields[i].in_tbl + row_id).val(v);
                        if(a_fields[i].tbl_type.indexOf("t"))
                            $(a_fields[i].in_tbl + row_id).text(v);
                        if(a_fields[i].tbl_type.indexOf("i"))
                            $(a_fields[i].in_tbl + row_id).attr("title",v);
                        if(a_fields[i].tbl_type.indexOf("x"))   {
                            var t = $("#"+a_fields[i].id).text();
                            $(a_fields[i].l_tbl + row_id).text(t);
                        }
                    }
                }
            }
            else
                alert("Пустой ответ!");
        }
    }
    else {
        console_debug_log(textStatus);
    }

    jqxr = null;
    $("#dialog_box").hide();
}

function tx_submit()
{
    var i;
    var f,v;
    var inv_idx = -1;
    var msg = "";
    var reqStr = "FRM_MODE=update&HIDDEN_ID=" + $("#HIDDEN_ID").val();
    for(i=0; i<a_fields.length; i++)    {
        f = $("#" + a_fields[i].id);
        v = f.val();
        if(v!==null&&v!==undefined) {
            if(v.length>a_fields[i].len)    {
                if(a_fields[i].type.indexOf("n")==0)    {
                    try {
                        var nn = new Number(v);
                        if(nn<=a_fields[i].val)   {
                            inv_idx = i;
                            msg = "Недопустимое значение <=" + a_fields[i].val.toString();
                        }
                        else {
                            reqStr += ("&" + a_fields[i].id + "=" + encodeURIComponent(v));
                        }

                    } catch (e) {
                        inv_idx = i;
                        msg = e.toString();
                    }
                }
                else {
                    reqStr += ("&" + a_fields[i].id + "=" + encodeURIComponent(v));
                }
            }
            else    {
                inv_idx = i;
                msg = "Слишком короткое значение"
            }
        }
        else    {
            inv_idx = i;
            msg = "Значение не установлено"
        }
    }
    if(inv_idx>=0&&inv_idx<a_fields.length)  {
        if(msg.length>0)
            alert(msg);
        $("#" + a_fields[i].id).select();
        return;
    }
    jqxr = $.ajax({
        type: "POST",
        url: "wimm_edit2.php",
        data: reqStr,
        complete: onTxComplete,
    });
}

function sel_row(row_id)
{
    var objDiv = document.getElementById("dialog_box");
    objDiv.style.top = (f_get_scroll_y()+200).toString()+"px";
    //var x = (window.innerWidth||document.body.clientWidth);
    var x = (f_get_scroll_x()-600)/2+500;
    if(x<0)
        x = 500;
    objDiv.style.left = x.toString()+"px";
    objDiv.style.display="inline";
    var s1;
    var i;
    if(row_id===null || row_id===undefined || row_id.length<1) {
        $("#FRM_MODE").val("insert");
        $("#HIDDEN_ID").val(0);
        $("#ADD_BTN").show();
        $("#OK_BTN").hide();
        $("#DEL_BTN").hide();
        $("#dlg_box_cap").text("Добавление записи");
        s1 = "";
        for(i=0; i<a_fields.length; i++)    {
            if(a_fields[i].id.indexOf("t_user")==0)
                $("#t_user").val($("#UID").val());
            else
                $("#" + a_fields[i].id).val(s1);
        }
    }
    else    {
        $("#FRM_MODE").val("update");
        $("#HIDDEN_ID").val(row_id);
        $("#ADD_BTN").hide();
        $("#OK_BTN").show();
        $("#DEL_BTN").show();
        $("#dlg_box_cap").text("Изменение записи");
        s1 = "";
        for(i=0; i<a_fields.length; i++)    {
            s1 = "";
            if(a_fields[i].tbl_type.indexOf("v")==0)
                s1 = $(a_fields[i].in_tbl + row_id).val();
            if(a_fields[i].tbl_type.indexOf("t")==0)
                s1 = $(a_fields[i].in_tbl + row_id).text();
            if(a_fields[i].tbl_type.indexOf("i")==0)
                s1 = $(a_fields[i].in_tbl + row_id).attr("title");
            console_debug_log(a_fields[i].in_tbl + row_id + "(" + a_fields[i].tbl_type + ")=" + s1);
            if(s1!==null && s1!==undefined && s1.length>0)
                $("#"+a_fields[i].id).val(s1);
        }
/*        $("#t_name").val($("#TNAME_" + row_id).text());
        $("#t_sum").val($("#T_SUMM_" + row_id).attr('title'));
        $("#t_user").val($("#T_USR_" + row_id).val());
        $("#t_place").val($("#T_PLACE_" + row_id).val());
        $("#t_budget").val($("#T_BUDG_" + row_id).val());
        $("#t_date").val($("#T_DATE_" + row_id).attr('title'));
        $('#t_type').val($("#T_TYPE_" + row_id).val());
        $('#t_curr').val($("#T_CURR_" + row_id).val());*/
    }
}

function del_click()
{
    var i;
    var f,v;
    var inv_idx = -1;
    var msg = "";
    var reqStr = "FRM_MODE=delete&HIDDEN_ID=" + $("#HIDDEN_ID").val();
    $("#FRM_MODE").val("delete");
    jqxr = $.ajax({
        type: "POST",
        url: "wimm_edit2.php",
        data: reqStr,
        complete: onTxComplete,
    });
}
