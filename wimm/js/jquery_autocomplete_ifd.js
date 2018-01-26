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
const cSuccessJA    = 'success';
const cUndefinedStr = 'undefined';
const cSharp        = '#';
const cEmptyStringJA= '';
const cPx           = 'px';
// direct styles
const cFormControl   = 'form-control';
const cNotFound       = 'not_found';
const cAutoCILink     = 'ac_link';
const cAutoCIBordered = 'ac_bordered';
// attributes
const cSelectedACItem = 'selected_ac_item';
const cAttrFor          = 'for';
const cAttrHeight       = 'height';
const cAttrScrollHeight = 'scroll_height';
const cAttrOverflow     = 'overflow';
const cAttrTop          = 'top';
const cAttrLeft         = 'left';
const cAttrWidth        = 'width';
// selectors
const cOKBtnSel         = '#OK_BTN';
const cFormCtlSel       = '.form-control';
const cDialogBoxText    = '#dlg_box_text';
const cVisibleSel       = ':visible';
// bound configuration
const cAttrBoundID    = 'bound_id';
const cAttrBoundDest  = 'bound_dest';
const cAttrBoundNodeT = 'bound_node_type';
const cAttrBoundNodeC = 'bound_node_class';
const cAttrBoundNodeN = 'bound_node_name';
// autocomplete item prefix
const cAutoCItemJA    = 'aci_';
/**
 * scroll container to item (emilate combo box behaviour
 * @param {type} containerID - container to scroll
 * @param {type} itemID - item to show
 * @returns {undefined} - nothing
 */
function scrollToItem(containerID, itemID)
{
    try {
        var container = $(cSharp+containerID);
        var scrollTo = $(cSharp+itemID);
        container.scrollTop(
            scrollTo.offset().top - container.offset().top + container.scrollTop()
        );
        
    } catch (e) {
        console.log(e.toString());
    }
}

/**
 * translate key code into action 
 * @param {Number} keyCode
 * @returns {Number}
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

/**
 * returns item index in the collection
 * @param {Array|Collection} items
 * @param {String} itemID - item ID
 * @returns {Number} -1 - no item or item index
 */
function getItemIndex(items, itemID)
{
    if(items)
    {
        var i;
        for(i=0; i<items.length; i++)
        {
            if(items[i].id===itemID)
                return i;
        }
    }
    return -1;
}

/**
 * apply action to autocomplete items
 * @param {String} boxID - autocomplete DIV id
 * @param {Number} how   - action to apply
 * @returns {Boolean} true if item selected or false if not
 */
function changeSelection(boxID, how)
{
    console.log(boxID + '=>' + how);
    var titem = cEmptyStringJA;
    var sel = $(cSharp+boxID).prop(cSelectedACItem);
    var items = $(cSharp+boxID).children();
    console.log('current selection:' + sel);
    if(items)
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
                if(i===-1)
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
                titem = $(cSharp+boxID).attr(cSelectedACItem);
                if(titem.indexOf(cAutoCItem)!==0)
                {
                    var titem2 = cAutoCItem + titem;
                    selectAcItem(boxID, titem2, $(cSharp+titem2).text());
                }
                else
                {
                    selectAcItem(boxID, titem, $(cSharp+titem).text());
                }
                return false;

            if(titem.length<1)
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
            console.log('new selection:' + titem);
            if(titem.length>0)
            {
                console.log('changeSelection:old selection: ' + sel);
                console.log('changeSelection:new selection: ' + titem);
                if(sel.length>0)
                    $(cSharp+sel).removeClass(cAutoCIBordered);
                $(cSharp+titem).addClass(cAutoCIBordered);
                scrollToItem(boxID, titem);
                $(cSharp+boxID).attr(cSelectedACItem, titem);
            }
        }
        return true;
    }
    else
    {
        console.log('No ITEMS!');
    }
    return false;
}
/**
 * select autocomplete items
 * @param {String} boxID    - an ID of the utocomplete box (usually DIV)
 * @param {String} itemID   - selected item's element ID
 * @param {String} itemText - selected item's element text
 * @param {boolean} callChange - disable call change handler if true
 * @returns nothing
 */
function selectAcItem(boxID, itemID, itemText, callChange)
{
    if((itemID===null || itemID===undefined || itemID===cUndefinedStr) &&
            (itemText===null || itemText===undefined || itemText===cUndefinedStr))
    {
        console.log('leaving selectAcItem(' +boxID+','+itemID+','+itemText+')');
        return ;
    }
    console.log('entering selectAcItem(' +boxID+','+itemID+','+itemText+')');
    var textTargetID = document.getElementById(boxID).getAttribute(cAttrFor);
    if(($(cSharp + boxID).is(cVisibleSel)))
    {
        var idTargetDest;
        if(textTargetID)
            idTargetDest = document.getElementById(textTargetID).getAttribute(cAttrBoundDest);
//        console.log('items set selectAcItem('+textTargetID+')');
        if(idTargetDest)
        {
            // advanced bind add subitem into target destionation
            console.log('advanced bind selectAcItem()');
            var nodeType = document.getElementById(textTargetID).getAttribute(cAttrBoundNodeT);
            var nodeClass = document.getElementById(textTargetID).getAttribute(cAttrBoundNodeC);
            var nodeName = document.getElementById(textTargetID).getAttribute(cAttrBoundNodeN);
            if(!nodeName)
                nodeName = "cid";
            var html = document.getElementById(idTargetDest).innerHTML;
            html += ("<" + nodeType + " class='" + nodeClass + "' id='" + itemID + 
                    "' title='Click to remove' onclick='$(this).remove();'>" + 
                    "<input type='hidden' name='" + nodeName + "[" + itemID + 
                    "]' value='" + itemID + "'>" +
                    itemText + "</" + nodeType + ">");
            document.getElementById(idTargetDest).innerHTML = html;
            document.getElementById(textTargetID).value = (cEmptyStringJA);
        }
        else
        {   // simple bind - text box for text + hidden for id
            console.log('simple bind selectAcItem()');
            var idTargetID = document.getElementById(textTargetID).getAttribute(cAttrBoundID);
            document.getElementById(textTargetID).value = (itemText);
            if(idTargetID)
            {
                if(itemID)
                {
                    if(itemID.indexOf(cAutoCItem)===0)
                    {
                        $(cSharp+idTargetID).val(itemID.substr(4));
                    }
                    else
                    {
                        $(cSharp+idTargetID).val(itemID);
                    }
                }
            }
            console.log('trigger change');
            if(!callChange)
            {
                $(cSharp+idTargetID).trigger("change");
            }
        }
        hideAcBox(boxID);
    }
    console.log('leaving selectAcItem()');
}

/**
 * selects next field when autocomplete finished
 * @param {String} jqSelector - selector to get input fields
 * @param {String} selID - current field ID
 * @param {String} jqSelector2 - object after last field
 * @returns {undefined}
 */
function    selectNextInput(jqSelector, selID, jqSelector2)
{
    console.log('selectNextInput('+jqSelector+','+selID+','+jqSelector2+')');
    var items = $(jqSelector);
    for(var i=0; i<items.length; i++)
    {
        if(items[i].id===selID)
        {
            console.log('selectNextInput found at '+i);
            if((i+1)<items.length)
            {
                console.log('selectNextInput '+items[i+1].id);
                $(cSharp+items[i+1].id).select();
            }
            else
            {
                $(jqSelector2).select();
            }
            break;
        }
    }
}
function    parseResponse(jsonData, textStatus, jqXHR, boxID)
{
    var callChange = false;
    if(!jsonData)
    {
        console.log('entering parseResponse() - null result');
        return ;
    }
    console.log('entering parseResponse()');
    var arr = jsonData;
    var i;
    var item;
    var sel_item_id;
    var item_text = cEmptyStringJA;
    var html = cEmptyStringJA;
    var o;
    var s1;
    var bShow = true;
    o = document.getElementById(boxID);
    if(o)
    {
        o.innerHTML = cEmptyStringJA;  // clear contents
        sel_item_id = o.getAttribute(cAttrFor);
        if(sel_item_id)
        {
            item_text = $(cSharp+sel_item_id).val();
        }
        sel_item_id = cEmptyStringJA;
        if(arr.length===1)
        {
            if(arr[0] && arr[0].id && arr[0].text)
            {
                sel_item_id = o.getAttribute(cAttrFor);
                $(cSharp+sel_item_id).removeClass(cNotFound);
                console.log('calling selectAcItem([0])');
                callChange = true;
                selectAcItem(boxID, arr[0].id, arr[0].text, callChange);
                selectNextInput(cFormCtlSel, sel_item_id, cOKBtnSel);
                bShow = false;
            }
            else
            {
                sel_item_id = o.getAttribute(cAttrFor);
                hideAcBox(boxID);
                $(cSharp+sel_item_id).addClass(cNotFound);
                bShow = false;
            }
        }
        else
        {
            sel_item_id = o.getAttribute(cAttrFor);
            $(cSharp+sel_item_id).removeClass(cNotFound);
            sel_item_id = cEmptyStringJA;
            console.log('updating div');
            for(i = 0; i < arr.length; i++)
            {
                if(sel_item_id.length<1 && item_text.length>0)
                {
                    if(arr[i].text.toString().indexOf(item_text)===0)
                        sel_item_id = arr[i].id;
                }
                s1 = "<a href=\"javascript:void(0);\" id=\"" + cAutoCItem + arr[i].id + "\"";
                if(arr[i].id.toString()===sel_item_id.toString())
                {
                    s1 += " class='"+cAutoCILink+" "+cAutoCIBordered+"' " +
                            "title='" + arr[i].text + "'>";
                }
                else
                {
                    s1 += " class='"+cAutoCILink+"'" +
                            "title='" + arr[i].text + "'>";
                }
                s1 += (arr[i].text + "</a>\n");
                html += s1;
            }
            o.innerHTML = html;
            $("."+cAutoCILink).click(function(e){
                var cid = e.currentTarget.id;
                var id = $(this).parent().attr(cAttrFor);
                selectAcItem(boxID, cid, $(this).text());
                if(id)
                {
                    selectNextInput(cFormCtlSel, id, cOKBtnSel);
                }
             });
            if(sel_item_id.length>0)
            {
                o.setAttribute(cSelectedACItem, sel_item_id);
                scrollToItem(boxID, sel_item_id);
            }
        }
    }
    else
        $(cSharp+boxID).text("!");
    $(cSharp+boxID).keyup(function(e)
    {
        var nCode = translateKeyCode(e.which);
        console.log('BOXKeyUp()');
        if(nCode!==0)
        {
            changeSelection(boxID, nCode);
        }
        //keyUpAcItem(boxID, sel);
    });
    var s1;
    var nMax;
    var n;
    s1 = $(cSharp + boxID).attr(cAttrScrollHeight);
    if(s1 && s1.length>0)
        nMax = Number(s1);
    else
        nMax = 50;
    s1 = $(cSharp + boxID).css( cAttrHeight );
    if (s1)
        n = Number(s1.replace(cPx,cEmptyStringJA));
    else
        n = 100500;
    if(n>nMax)
    {
        $(cSharp + boxID).css( cAttrHeight, nMax.toString() + cPx );
        $(cSharp + boxID).css( cAttrOverflow, "scroll");
    }
    else
    {
        $(cSharp + boxID).css( cAttrOverflow, "auto");
    }
    if(bShow===true)
        $(cSharp + boxID).show();
    console.log('leaving parseResponse()');
    if(callChange)
    {
        $(cSharp+idTargetID).trigger("change");
    }
}
/**
 * query autocomplete items, set key handlers
 * @param {string} boxID - autocomplete div id
 * @param {string} itemID - autocomplete text box id
 */
function queryAcItems(boxID, itemID)
{
    var query_src = $(cSharp+itemID).attr("ac_src");
    var query_str = cEmptyStringJA;
    var param_str = $(cSharp+itemID).attr("ac_params");
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
            if(pa[1].indexOf(cSharp)===0)
            {
                pv = $(pa[1]).val();
            }
            else
            {
                if(pa[1].length>0)
                    pv = pa[1];
                else
                    pv = $(cSharp+itemID).val();
            }
            if(pv)
            {
                if(query_str.length>0)
                    query_str += param_sep;
                query_str += pa[0] + "=" + pv;
            }
        }
        catch(err)
        {
            pv = cEmptyStringJA;
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
        cache: false,
        dataType: "json",
        data: query_str,
        success: function(jsonData, textStatus, jqXHR){
            console.log(cSuccessJA);
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
        if(ac_jqxhr)
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
    var textTargetID = $(cSharp+boxID).prop(cAttrFor);
    $(cSharp+boxID).hide();
    cancelQuery();
    if(textTargetID)
    {
        var vExit = 10;
        var vi = -1;
        var ctl = $(cDialogBoxText).find(cFormCtlSel);
        if(ctl)
        {
            for(var i=0; i<ctl.length; i++)
            {
                console.log('Control:'+ctl[i].id);
                if(vi>=0)
                {
                    ctl[i].focus();
                    vExit = -2;
                    break;
                }
                else
                {
                    if(ctl[i].id===textTargetID)
                    {
                        console.log('index of "'+ctl[i].id + '"='+i);
                        vi = i;
                    }
                }
            }
        }
        console.log('Exit code:'+vExit);
	document.getElementById(boxID).setAttribute(cAttrFor, cEmptyStringJA);
    }
    else
    {
        console.log('No target');
    }

}
/**
 * display autocomplete box under item
 * @param {string} boxID - autocomplete div id
 * @param {string} itemID - item under div will be displayed
 * @returns {undefined}
 */
function displayAcBox(boxID, itemID)
{
    var itemSel = cSharp + itemID;
    var boxSel = cSharp + boxID;
    if(itemSel.length>2 && // something to find
            (!($(boxSel).is(cVisibleSel)) || // box not visible
            $(boxSel).attr(cAttrFor)!==itemID)) // box not under item
    {
        var p;
        if($(itemSel).hasClass( cFormControl ))
            p = $(itemSel).position();
        else
            p = $(itemSel).offset();
        if(p)
        {
            console.log('item:'+ itemID + ', top:' + p.top.toString() + ', left:' + p.left.toString());
            var s1 = $(itemSel).css( cAttrHeight );
            var n1 = 0;
            n1 = Number(s1.replace(cPx,cEmptyStringJA));
            n1 += n1;
            n1 += Number(p.top);
            p.top =  n1 ;
//            $("#ac").offset({ top: p.top, left: p.left});
            $(boxSel).offset({ top: p.top, left: p.left});
            console.log('box:'+ boxID + ', top:' + p.top.toString() + ', left:' + p.left.toString());
            $(boxSel).css(cAttrTop, p.top);
            $(boxSel).css(cAttrLeft, p.left);
            s1 = $(itemSel).css( cAttrWidth );
            $(boxSel).css(cAttrWidth, s1);
            $(boxSel).show();
            $(boxSel).attr(cAttrFor, itemID);
            $(cSharp+boxID).attr(cSelectedACItem, cEmptyStringJA);
        }
        else
        {
            console.log('no offset nor position defined');
        }
    }
    queryAcItems(boxID, itemID);
}

function textFieldKeyUp(event)
{
    var nCode = translateKeyCode(event.which);
    var itemID;
    var itemText;
    console.log('entering textFieldKeyUp(' + nCode.toString() + ')');
    if(nCode===-4 || nCode===5)
    {
//        console.log('<Esc>');
        hideAcBox(ac_box);
    }
    if(nCode===-4)
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
                displayAcBox(ac_box,event.currentTarget.id);
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
        case 4:
            itemID = $(cSharp+ac_box).attr(cSelectedACItem);
            if(itemID && itemID.length>0)
            {
                itemText = $(cSharp+itemID).text();
                if(itemText)
                {
                    var sel_item_id = $(cSharp+ac_box).attr(cAttrFor);
                    selectAcItem(ac_box, itemID, itemText);
                    selectNextInput(cFormCtlSel, sel_item_id, cOKBtnSel);
                }
            }
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
        if(event.keyCode === 13)
        {
            changeSelection(ac_box, 4);
            event.preventDefault();
            return false;
        }
    });
    $(ac_txt).focus(function(event)
    {
        if($(cSharp+ac_box).prop(cAttrFor)!==event.currentTarget.id)
        {
            hideAcBox(ac_box);
        }
    });
}
