/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function my_form_validate(frm_name)
{
    var ret = true;
    var elem = document.getElementById(frm_name).elements;
    for(var i = 0; i < elem.length; i++)    {
        if(elem[i].required&&elem[i].value.toString().length<=0)    {
            elem[i].focus();
            alert(elem[i].name + " is not valid! ");
            ret = false;
            break;
        }
    }
    return ret;
}

function submit_myform_c(frm_id, act, f_act, have2check)
{
    var post_enabled = true;
    var my_form = document.getElementById(frm_id);
    if(have2check)
        post_enabled = my_form_validate(frm_id);
    if(post_enabled)    {
        my_form.action=act;
        my_form.FRM_ACTION.value=f_act;
        my_form.submit();        
    }
}

function submit_myform(frm_id, act, f_act)
{
    var my_form = document.getElementById(frm_id);
    my_form.action=act;
    my_form.FRM_MODE.value=f_act;
    my_form.submit();        
    //submit_myform_c(frm_id, act, f_act, false);
}

function showNavBar(elem_id,btn_id)
{
    elem = document.getElementById(elem_id);
    ebtn = document.getElementById(btn_id);
    if (elem.style.display === "inline") {
        ebtn.src=ebtn.src.replace("hide_up.gif","drop_down.gif");//"picts/drop_down.gif";
        elem.style.display="none";
        ebtn.title='Показать панель';
    }
    else    {
        ebtn.src=ebtn.src.replace("drop_down.gif","hide_up.gif");//"picts/hide_up.gif";
        elem.style.display="inline";
        ebtn.title='Скрыть панель';
    }
}

function enable_element(elem_id)
{
    elem = document.getElementById(elem_id);
    elem.disabled=false;
}
function get_default_form()
{
    if(navigator.appName.indexOf("Internet Explorer")!==-1){
        my_form = document.forms.item(0);
    }
    else
        my_form = document.forms[0];
    return my_form;
}
function submit_def_form(act, f_act)
{
    var my_form = get_default_form();
    my_form.action=act;
    my_form.FRM_ACTION.value=f_act;
    my_form.submit();        
}

function show_hide_elem(elem_id)
{
    var elem = document.getElementById(elem_id);
    if(elem!==null) {
        if (elem.style.display === "inline") {
            elem.style.display="none";
        }
        else    {
            elem.style.display="inline";
        }
    }
    else
        alert(elem_id);
}

function select_radio_button(elem_name, elem_value)
{
    var x=document.getElementsByName(elem_name);
            for(i=0; i<x.length; i++)   {
        if(x.item(i).value===elem_value) {
            x.item(i).checked = true;
            break;
        }
    }
}

function select_single_option(elem_id,elem_value)
{
    var objSel = document.getElementById(elem_id);
    for(i=0; i<objSel.length; i++)    {
        //num2 = new Number(objSel.options[i].value);
        if(objSel.options[i].value===elem_value) {
            objSel.options[i].selected = true;
            break;
        }
        else
            objSel.options[i].selected = false;
    }    
}

function f_get_scroll_y()
{
    return (window.pageYOffset || document.body.scrollTop);
}

function f_get_scroll_x()
{
    return (window.innerWidth||document.body.clientWidth);
}

function set_elem_disp(elem_id, disp)
{
    var elem = document.getElementById(elem_id);
    if(elem!==null) {
        if (elem.style.display.toString() !== disp.toString()) {
            elem.style.display=disp.toString();
        }
    }
}

function show_elem(elem_id)
{
    set_elem_disp(elem_id,"inline");
}

function hide_elem(elem_id)
{
    set_elem_disp(elem_id,"none");
}

function getElementsByClassName(classname, node)  {
    if(!node) node = document.getElementsByTagName("body")[0];
    var a = [];
    var re = new RegExp('\\b' + classname + '\\b');
    var els = node.getElementsByTagName("*");
    for(var i=0,j=els.length; i<j; i++)
        if(re.test(els[i].className))a.push(els[i]);
    return a;
}

function show_by_class(mode, cls_name)
{
    var elements = new Array();
    elements = getElementsByClassName(cls_name);
    for(i in elements ){
         elements[i].style.display = mode;
    }                
}

function get_elem_value(elem_id)
{
    var s1 = "";
    if(elem_id!==null)   {
        if(elem_id.length>0)    {
            var field = document.getElementById(elem_id);
            s1 = field.value;
        }
    }
    return s1;
}

function get_elem_text(elem_id)
{
    var s1 = "";
    if(elem_id!==null)   {
        if(elem_id.length>0)    {
            var field = document.getElementById(elem_id);
            s1 = field.innerHTML;
        }
    }
    return s1;
}

function set_elem_value(elem_id, val)
{
    if(elem_id!==null)   {
        if(elem_id.length>0)    {
            var field = document.getElementById(elem_id);
            field.value = val;
        }
    }
}

function set_elem_text(elem_id, txt)
{
    if(elem_id!==null)   {
        if(elem_id.length>0)    {
            var field = document.getElementById(elem_id);
            field.innerHTML = txt;
        }
    }
}

function delete_table_row()
{
    var id=$("#e_row_id").val();
    $("#row_"+id).parent().parent().remove();
}
function table_row_selected(sel_id, form_id)
{
    var db_id = $(sel_id).val();
    var form_fields = $(form_id).find(".form_field");
    var i;
    var bt;
    var bi;
    for(i=0; i<form_fields.length; i++)
    {
        bt = form_fields[i].getAttribute("bind_row_type");
        bi = "#" + form_fields[i].getAttribute("bind_row_id") + db_id;
        console.log("bind type="+bt+", id="+bi);
        switch(bt)
        {
        case 'value':
            form_fields[i].value = ($(bi).val());
            break;
        case 'label':
            form_fields[i].value = ($(bi).text());
            break;
        case 'title':
            form_fields[i].value = ($(bi).attr('title'));
            break;
        }
    }
}
