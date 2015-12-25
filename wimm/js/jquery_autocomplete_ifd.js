/*
 * Yet another autocomplete jQuery script
 * 
 * custrom attrubutes, used by this script:
 * 
 * DIV.for - text box for which autocomplete DIV open for
 * DIV.selected_ac_item - last item selected
 * DIV.scroll_height - height limit (in pixels), after which scrollbars shown
 * 
 * INPUT.bound_dest       - DOM element that receive selected autocomplete item
 * INPUT.bound_node_type  - newly created DOM element type for selected autocomplete item
 * INPUT.bound_node_class - newly created DOM element class for selected autocomplete item
 * INPUT.bound_node_name  - constant part name for created in advanced binding nodes
 * INPUT.bound_id  - HIDDEN/TEXT control, received item id
 * INPUT.ac_src    - autocomplete source URL
 * INPUT.ac_params - semicolon separated string of parameters "name=value" 
 *                   kind if value begins with '#' it means that value 
 *                   will be acquired from DOM element value
 */
var ac_jqxhr = null; // AJAX query handler
var ac_box = null; // autocomplete div-box id
var ac_txt = null; // autocomplete text box style

/**
 * scroll container to item (emilate combo box behaviour
 * @param {type} containerID - container to scroll
 * @param {type} itemID - item to show
 * @returns {undefined} - nothing
 */
function scrollToItem(containerID, itemID)
{
    try {
        var container = $('#'+containerID);
        var scrollTo = $('#'+itemID);
        container.scrollTop(
            scrollTo.offset().top - container.offset().top + container.scrollTop()
        );
        
    } catch (e) {
        console.log(e.toString());
    }
}
/**
 * action after autocomplete item has been selected
 * @param {string} boxID - autocomplete div id
 * @param {string} itemID - selected autocomplete item id
 * @param {string} itemText - selected autocomplete item text
 */
function translateKeyCode(keyCode)
{
    var nRet = 0;
    switch(keyCode)
    {
        case 9: // tab
            nRet = 5;
            break;
        case 13: // enter
//                case 10: // enter
            nRet = 4;
            break;
        case 27: // escape
            nRet = -4;
            break;
        case 33: // page up
            nRet = -3;
            break;
        case 34: // page down
            nRet = 3;
            break;
        case 35: // end
            nRet = 2;
            break;
        case 36: // home
            nRet = -2;
            break;
        case 38: // up arrow
            nRet = -1;
            break;
        case 40: // down arrow
            nRet = 1;
            break;
    }
    return nRet;
}

function getItemIndex(items, itemID)
{
    if(items!=null)
    {
        var i;
        for(i=0; i<items.length; i++)
        {
            if(items[i].id==itemID)
                return i;
        }
    }
    return -1;
}

function changeSelection(boxID, how)
{
    var titem = "";
    var sel = document.getElementById(boxID).getAttribute("selected_ac_item");
    var items = $("#"+boxID).children(".ac_link");
    if(items!=null)
    {
        var i = -1;
        switch(how)
        {
            case -2:
                titem = items[0].id;
                break;
            case 2:
                titem = items[items.length - 1].id;
                break;
            case -1:
            case 1:
                i = getItemIndex(items, sel);
                if(i==-1)
                    i = 0;
                else
                    i += how;
                break;
            case 3:
                i = getItemIndex(items, sel) + 11;
                break;
            case -3:
                i = getItemIndex(items, sel) - 11;
                break;
            case 4:
                titem = 'aci_' + $("#"+boxID).attr("selected_ac_item");
                selectAcItem(boxID, titem, $("#"+titem).text());
                return false;

        }
        if(titem.length<1)
        {
            if(items!=null && items!=undefined && items.length>0)
            {
                if(i<0)
                {
                    i = 0;
                }
                else
                {
                    if(i>items.length)
                        i = items.length - 1;
                }
                titem = items[i].id;
            }
        }
        if(titem.length>0)
        {
//            console.log('changeSelection:old selection: ' + sel);
//            console.log('changeSelection:new selection: ' + titem);
            $("#"+sel).removeClass("ac_bordered");
            $("#"+titem).addClass("ac_bordered");
            scrollToItem(boxID, titem);
            $("#"+boxID).attr("selected_ac_item", titem);
        }
    }
    return true;
}
function selectAcItem(boxID, itemID, itemText)
{
    console.log('entering selectAcItem(' +boxID+','+itemID+','+itemText+')');
    if(($("#" + boxID).is(":visible")))
    {
        var textTargetID = document.getElementById(boxID).getAttribute("for");
        var idTargetDest;
        if(textTargetID!=null && textTargetID.length>0)
            idTargetDest = document.getElementById(textTargetID).getAttribute("bound_dest");
//        console.log('items set selectAcItem('+textTargetID+')');
        if(idTargetDest!=null && idTargetDest.length>0)
        {
            // advanced bind add subitem into target destionation
            console.log('advanced bind selectAcItem()');
            var nodeType = document.getElementById(textTargetID).getAttribute("bound_node_type");
            var nodeClass = document.getElementById(textTargetID).getAttribute("bound_node_class");
            var nodeName = document.getElementById(textTargetID).getAttribute("bound_node_name");
            if(nodeName==null || nodeName.length>0)
                nodeName = "cid";
            var html = document.getElementById(idTargetDest).innerHTML;
            html += ("<" + nodeType + " class='" + nodeClass + "' id='" + itemID + 
                    "' title='Click to remove' onclick='$(this).remove();'>" + 
                    "<input type='hidden' name='" + nodeName + "[" + itemID + 
                    "]' value='" + itemID + "'>" +
                    itemText + "</" + nodeType + ">");
            document.getElementById(idTargetDest).innerHTML = html;
            document.getElementById(textTargetID).value = ('');
        }
        else
        {   // simple bind - text box for text + hidden for id
            console.log('simple bind selectAcItem()');
            var idTargetID = document.getElementById(textTargetID).getAttribute("bound_id");
            document.getElementById(textTargetID).value = (itemText);
            if(idTargetID!=null && idTargetID.length>0)
            {
                if(itemID.substr(0,4)=='aci_')
                {
                    itemID = itemID.substr(4);
                }                
                $('#'+idTargetID).val(itemID);
            }
        }
        hideAcBox(boxID);
    }
    console.log('leaving selectAcItem()');
}
function    parseResponse(jsonData, textStatus, jqXHR, boxID)
{
    if(jsonData==null || jsonData === undefined)
    {
        console.log('entering parseResponse() - null result');
        return ;
    }
    console.log('entering parseResponse()');
    var arr = jsonData;
    var i;
    var item;
    var sel_item_id;
    var item_text = "";
    var html = "";
    var o;
    var s1;
    o = document.getElementById(boxID);
    if(o!=null)
    {
        o.innerHTML = "";  // clear contents
        sel_item_id = o.getAttribute("for");
        if(sel_item_id!=null && sel_item_id.length>0)
        {
            item_text = $("#"+sel_item_id).val();
        }
        sel_item_id = "";
        for(i = 0; i < arr.length; i++)
        {
            if(sel_item_id.length<1 && item_text.length>0)
            {
                if(arr[i].text.toString().indexOf(item_text)==0)
                    sel_item_id = arr[i].id;
            }
            s1 = "<a href='#' id='aci_" + arr[i].id;
            var jsc = " onclick=\"selectAcItem('" + boxID + "', 'aci_" +arr[i].id + "','"+
                    arr[i].text + "');\" ";
            if(arr[i].id.toString()==sel_item_id.toString())
            {
                s1 += "' class='ac_link ac_bordered'" + jsc +">";
            }
            else
            {
                s1 += "' class='ac_link'" + jsc +">";
            }
            s1 += (arr[i].text + "</a>");
            html += s1;
        }
        o.innerHTML = html;
        if(sel_item_id.length>0)
        {
            o.setAttribute("selected_ac_item", sel_item_id);
            scrollToItem(boxID, sel_item_id);
        }
    }
    else
        $("#"+boxID).text("!");
    $(".ac_link").click(function(e)
    {
        console.log('acLink click');
        selectAcItem(boxID, 'aci_' + e.currentTarget.id, $(this).text());
    });
    $(".ac_link").keyup(function(e)
    {
        var nCode = translateKeyCode(e.which);
        console.log('LINKKeyUp()');
        if(nCode!=0)
        {
            changeSelection(boxID, nCode);
        }
        //keyUpAcItem(boxID, e.currentTarget.id);
        console.log('acLink onKeyUp()');
    });
    $("#"+boxID).keyup(function(e)
    {
        var nCode = translateKeyCode(e.which);
        console.log('BOXKeyUp()');
        if(nCode!=0)
        {
            changeSelection(boxID, nCode);
        }
        //keyUpAcItem(boxID, sel);
    });
    var s1;
    var nMax;
    var n;
    s1 = $("#" + boxID).attr("scroll_height");
    if(s1!=null && s1.length>0)
        nMax = Number(s1);
    else
        nMax = 50;
    s1 = $("#" + boxID).css( "height" );
    if (s1!=null && s1!=undefined)
        n = Number(s1.replace("px",""));
    else
        n = 100500;
    if(n>nMax)
    {
        $("#" + boxID).css( "height", nMax.toString() + "px" );
        $("#" + boxID).css( "overflow", "scroll");
    }
    else
    {
        $("#" + boxID).css( "overflow", "auto");
    }
    $("#" + boxID).show();
    console.log('leaving parseResponse()');
}
/**
 * query autocomplete items, set key handlers
 * @param {string} boxID - autocomplete div id
 * @param {string} itemID - autocomplete text box id
 */
function queryAcItems(boxID, itemID)
{
    var query_src = $("#"+itemID).attr("ac_src");
    var query_str = "";
    var param_str = $("#"+itemID).attr("ac_params");
    var params = param_str.split(";");
    var i;
    var pa;
    var pv;
    var param_sep = "&";
    console.log('entering queryAcItems()');
    // combine query string
    for(i=0; i<params.length; i++)
    {
        if(params[i].length<1)
            continue;
        try
        {
            pa = params[i].split("=");
            if(pa[1].indexOf("#")==0)
            {
                pv = $(pa[1]).val();
            }
            else
            {
                if(pa[1].length>0)
                    pv = pa[1];
                else
                    pv = $("#"+itemID).val();
            }
            if(pv!=null && pv.length>0)
            {
                if(query_str.length>0)
                    query_str += param_sep;
                query_str += pa[0] + "=" + pv;
            }
        }
        catch(err)
        {
            pv = "";
        }
    }
    var d = new Date();
    query_str += param_sep;
    query_str += "d=" + d.toString();
    console.log('params parsed: ' + query_str);
    console.log('query to: ' + query_src);
    // got query string - send request
    ac_jqxhr =  $.ajax({
        type: "POST",
        url: query_src,
        dataType: "json",
        data: query_str,
        success: function(jsonData, textStatus, jqXHR){
            console.log('success');
            parseResponse(jsonData, textStatus, jqXHR, boxID);
        }
    });
    console.log('leaving queryAcItems()');
}
/**
 * cancel item request (if any)
 * @returns {undefined}
 */
function cancelQuery()
{
    try {
        if(ac_jqxhr!=null)
            ac_jqxhr.abort();
        ac_jqxhr = null;    
    } catch (e) {
        ac_jqxhr = null;
    }
}
/**
 * hide autocomplete box, cancel request
 * @param {string} boxID - autocomplete div id
 * @returns {undefined}
 */
function hideAcBox(boxID)
{
    $("#"+boxID).hide();
    cancelQuery();
}
/**
 * display autocomplete box under item
 * @param {string} boxID - autocomplete div id
 * @param {string} itemID - item under div will be displayed
 * @returns {undefined}
 */
function displayAcBox(boxID, itemID)
{
    var itemSel = "#" + itemID;
    var boxSel = "#" + boxID;
    if(itemSel.length>2 && // something to find
            (!($(boxSel).is(":visible")) || // box not visible
            $(boxSel).attr("for")!=itemID)) // box not under item
    {
        var p;
        if($(itemSel).hasClass( "form-control" ))
            p = $(itemSel).position();
        else
            p = $(itemSel).offset();
        console.log('item:'+ itemID + ', top:' + p.top.toString() + ', left:' + p.left.toString())
        var s1 = $(itemSel).css( "height" );
        var n1 = 0;
        n1 = Number(s1.replace("px",""));
        n1 += n1;
        n1 += Number(p.top);
        p.top =  n1 ;
        $("#ac").offset({ top: p.top, left: p.left});
        console.log('box:'+ boxID + ', top:' + p.top.toString() + ', left:' + p.left.toString())
        $(boxSel).css("top", p.top);
        $(boxSel).css("left", p.left);
        s1 = $(itemSel).css( "width" );
        $(boxSel).css("width", s1);
        $(boxSel).show();
        $(boxSel).attr("for", itemID);
        $("#"+boxID).attr("selected_ac_item", "");
    }
    queryAcItems(boxID, itemID);
}

function textFieldKeyUp(event)
{
    var nCode = translateKeyCode(event.which);
    console.log('entering textFieldKeyUp(' + nCode.toString() + ')');
    if(nCode==-4 || nCode==5)
    {
//        console.log('<Esc>');
        hideAcBox(ac_box);
    }
    if(nCode==-4)
    {
        event.preventDefault();
    }
//    else if(nCode==4)
//    {
//        if(!changeSelection(ac_box, nCode))
//        {
//            event.preventDefault();
//            return false;
//        }
//    }
    switch(nCode)
    {
        case 0:
            if(event.currentTarget.value.length>2)
            {
                displayAcBox(ac_box,event.currentTarget.id)
            }
            else
            {
                nCode = -4;
            }
            break;
        case 2:
        case -2:
            break;
        case 1: // up
        case 3: // page up
        case -1: // down
        case -3: // page down
            if(!changeSelection(ac_box, nCode))
            {
                event.preventDefault();
            }
            break;
    }
    console.log('leaving textFieldKeyUp()');
    return true;
}

function ac_init(boxID, ac_box_sel)
{
    ac_box = boxID;
    ac_txt = ac_box_sel;
    $(ac_txt).keyup(function(event)
    {
        return textFieldKeyUp(event);
    });
    $(ac_txt).keypress(function(event)
    {
        if(event.keyCode == 13)
        {
            changeSelection(ac_box, 4);
            event.preventDefault();
            return false;
        }
    });
    $(ac_txt).blur(function(event)
    {
        hideAcBox(ac_box);
    });
}
