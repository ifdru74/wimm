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

class AutoCompleteIFD
{
    constructor(boxID, ac_box_sel, item_title)
    {
        this.timeout = 600;
        this.change_timer = null;
        this.call_change = false;
        this.ac_jqxhr = null; // AJAX query handler
        this.ac_box = boxID; // autocomplete div-box id
        this.ac_box_id = AutoCompleteIFD.cSharp+boxID; // autocomplete div-box id
        this.ac_txt = ac_box_sel; // autocomplete text box style
        this.item_title = item_title;
        if(!this.item_title)
            this.item_title = 'Click to remove';
        var pThis = this;
        $(this.ac_txt).keyup(function(event)
        {
            return pThis.textFieldKeyUp(event);
        });
        $(this.ac_txt).keypress(function(event)
        {
            if(event.keyCode === 13)
            {
                pThis.changeSelection(4);
                event.preventDefault();
                return false;
            }
        });
        $(this.ac_txt).focus(function(event)
        {
            if($(pThis.ac_box_id).prop(AutoCompleteIFD.cAttrFor)!==event.currentTarget.id)
            {
                pThis.hideAcBox();
            }
        });
    };
    /**
     * scroll container to item (emilate combo box behaviour
     * @param {type} itemID - item to show
     * @returns {undefined} - nothing
     */
    scrollToItem(itemID)
    {
        try {
            var container = $(this.ac_box_id);
            var scrollTo = $(AutoCompleteIFD.cSharp+itemID);
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
    translateKeyCode(keyCode)
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
    getItemIndex(items, itemID)
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
     * @param {Number} how   - action to apply
     * @returns {Boolean} true if item selected or false if not
     */
    changeSelection(how)
    {
        console.log(this.ac_box + '=>' + how);
        var titem = AutoCompleteIFD.cEmptyString;
        var sel = $(this.ac_box_id).prop(AutoCompleteIFD.cSelectedACItem);
        var items = $(this.ac_box_id).children();
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
                    i = this.getItemIndex(items, sel);
                    if(i===-1)
                        i = 0;
                    else
                        i += how;
                    break;
                case 3:
                    i = this.getItemIndex(items, sel) + 11;
                    break;
                case -3:
                    i = this.getItemIndex(items, sel) - 11;
                    break;
                case 4:
                    titem = $(this.ac_box_id).attr(AutoCompleteIFD.cSelectedACItem);
                    if(titem.indexOf(AutoCompleteIFD.cAutoCItem)!==0)
                    {
                        var titem2 = AutoCompleteIFD.cAutoCItem + titem;
                        this.selectAcItem(titem2, $(AutoCompleteIFD.cSharp+titem2).text());
                    }
                    else
                    {
                        this.selectAcItem(titem, $(AutoCompleteIFD.cSharp+titem).text());
                        return false;
                    }
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
            }
            console.log('new selection:' + titem);
            if(titem.length>0)
            {
                console.log('changeSelection:old selection: ' + sel);
                console.log('changeSelection:new selection: ' + titem);
                if(sel)
                    $(AutoCompleteIFD.cSharp+sel).removeClass(AutoCompleteIFD.cAutoCIBordered);
                $(AutoCompleteIFD.cSharp+titem).addClass(AutoCompleteIFD.cAutoCIBordered);
                this.scrollToItem(titem);
                $(this.ac_box_id).attr(AutoCompleteIFD.cSelectedACItem, titem);
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
     * @param {String} itemID   - selected item's element ID
     * @param {String} itemText - selected item's element text
     * @returns nothing
     */
    selectAcItem(itemID, itemText)
    {
        if((itemID===null || itemID===undefined || itemID===AutoCompleteIFD.cUndefinedStr) &&
                (itemText===null || itemText===undefined || itemText===AutoCompleteIFD.cUndefinedStr))
        {
            console.log('leaving selectAcItem('+itemID+','+itemText+')');
            return ;
        }
        console.log('entering selectAcItem('+itemID+','+itemText+') '+AutoCompleteIFD.cAttrFor);
        var textTargetID = document.getElementById(this.ac_box).getAttribute(AutoCompleteIFD.cAttrFor);
        if(($(this.ac_box_id).is(AutoCompleteIFD.cVisibleSel)))
        {
            var idTargetDest;
            if(textTargetID)
                idTargetDest = document.getElementById(textTargetID).getAttribute(AutoCompleteIFD.cAttrBoundDest);
    //        console.log('items set selectAcItem('+textTargetID+')');
            if(idTargetDest)
            {
                // advanced bind add subitem into target destionation
                console.log('advanced bind selectAcItem()');
                var nodeType = document.getElementById(textTargetID).getAttribute(AutoCompleteIFD.cAttrBoundNodeT);
                var nodeClass = document.getElementById(textTargetID).getAttribute(AutoCompleteIFD.cAttrBoundNodeC);
                var nodeName = document.getElementById(textTargetID).getAttribute(AutoCompleteIFD.cAttrBoundNodeN);
                if(!nodeName)
                    nodeName = "cid";
                var html = document.getElementById(idTargetDest).innerHTML;
                html += ("<" + nodeType + " class='" + nodeClass + "' id='" + itemID + 
                        "' title='"+this.item_title+"' onclick='$(this).remove();'>" + 
                        "<input type='hidden' name='" + nodeName + "[" + itemID + 
                        "]' value='" + itemID + "'>" +
                        itemText + "</" + nodeType + ">");
                document.getElementById(idTargetDest).innerHTML = html;
                document.getElementById(textTargetID).value = (AutoCompleteIFD.cEmptyString);
            }
            else
            {   // simple bind - text box for text + hidden for id
                var idTargetID = document.getElementById(textTargetID).getAttribute(AutoCompleteIFD.cAttrBoundID);
                console.log('simple bind selectAcItem(' + idTargetID + ')');
                document.getElementById(textTargetID).value = (itemText);
                if(idTargetID)
                {
                    if(itemID)
                    {
                        if(itemID.indexOf(AutoCompleteIFD.cAutoCItem)===0)
                        {
                            $(AutoCompleteIFD.cSharp+idTargetID).val(itemID.substr(4));
                        }
                        else
                        {
                            $(AutoCompleteIFD.cSharp+idTargetID).val(itemID);
                        }
                    }
                }
                if(this.call_change)
                {
                    var pThis = this;
                    console.log('postpone changes');
                    this.change_timer = setTimeout(function(){ 
                        console.log('invoke changes');
                        $(AutoCompleteIFD.cSharp+idTargetID).trigger("change");
                        clearTimeout(pThis.change_timer);
                    }, pThis.timeout);
                }
            }
            this.hideAcBox();
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
    selectNextInput(jqSelector, selID, jqSelector2)
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
                    $(AutoCompleteIFD.cSharp+items[i+1].id).select();
                }
                else
                {
                    $(jqSelector2).select();
                }
                break;
            }
        }
    }
    parseResponse(jsonData)
    {
        if(!jsonData)
        {
            console.log('entering parseResponse() - null result');
            return ;
        }
        console.log('entering parseResponse()');
        var arr = jsonData;
        var i;
        var sel_item_id;
        var item_text = AutoCompleteIFD.cEmptyString;
        var html = AutoCompleteIFD.cEmptyString;
        var o;
        var s1;
        var bShow = true;
        o = document.getElementById(this.ac_box);
        if(o)
        {
            o.innerHTML = AutoCompleteIFD.cEmptyString;  // clear contents
            sel_item_id = o.getAttribute(AutoCompleteIFD.cAttrFor);
            if(sel_item_id)
            {
                item_text = $(AutoCompleteIFD.cSharp+sel_item_id).val();
            }
            sel_item_id = AutoCompleteIFD.cEmptyString;
            if(arr.length===1)
            {
                if(arr[0] && arr[0].id && arr[0].text)
                {
                    sel_item_id = o.getAttribute(AutoCompleteIFD.cAttrFor);
                    $(AutoCompleteIFD.cSharp+sel_item_id).removeClass(AutoCompleteIFD.cNotFound);
                    console.log('calling selectAcItem([0])');
                    this.selectAcItem(arr[0].id, arr[0].text);
                    this.selectNextInput(AutoCompleteIFD.cFormCtlSel, sel_item_id, AutoCompleteIFD.cOKBtnSel);
                    bShow = false;
                }
                else
                {
                    sel_item_id = o.getAttribute(AutoCompleteIFD.cAttrFor);
                    this.hideAcBox();
                    $(AutoCompleteIFD.cSharp+sel_item_id).addClass(AutoCompleteIFD.cNotFound);
                    bShow = false;
                }
            }
            else
            {
                sel_item_id = o.getAttribute(AutoCompleteIFD.cAttrFor);
                $(AutoCompleteIFD.cSharp+sel_item_id).removeClass(AutoCompleteIFD.cNotFound);
                sel_item_id = AutoCompleteIFD.cEmptyString;
                console.log('updating div');
                for(i = 0; i < arr.length; i++)
                {
                    if(sel_item_id.length<1 && item_text.length>0)
                    {
                        if(arr[i].text.toString().indexOf(item_text)===0)
                            sel_item_id = arr[i].id;
                    }
                    s1 = "<a href=\"javascript:void(0);\" id=\"" + AutoCompleteIFD.cAutoCItem + arr[i].id + "\"";
                    if(arr[i].id.toString()===sel_item_id.toString())
                    {
                        s1 += " class='"+AutoCompleteIFD.cAutoCILink+" "+AutoCompleteIFD.cAutoCIBordered+"' " +
                                "title='" + arr[i].text + "'>";
                    }
                    else
                    {
                        s1 += " class='"+AutoCompleteIFD.cAutoCILink+"'" +
                                "title='" + arr[i].text + "'>";
                    }
                    s1 += (arr[i].text + "</a>\n");
                    html += s1;
                }
                o.innerHTML = html;
                var pThis = this;
                $("."+AutoCompleteIFD.cAutoCILink).click(function(e){
                    var cid = e.currentTarget.id;
                    var id = $(AutoCompleteIFD.cSharp + cid).parent().attr(AutoCompleteIFD.cAttrFor);
                    pThis.selectAcItem(cid, $(AutoCompleteIFD.cSharp + cid).text());
                    if(id)
                    {
                        pThis.selectNextInput(AutoCompleteIFD.cFormCtlSel, id, AutoCompleteIFD.cOKBtnSel);
                    }
                 });
                if(sel_item_id.length>0)
                {
                    o.setAttribute(AutoCompleteIFD.cSelectedACItem, sel_item_id);
                    pThis.scrollToItem(sel_item_id);
                }
            }
        }
        else
            $(this.ac_box_id).text("!");
        $(this.ac_box_id).keyup(function(e)
        {
            var nCode = this.translateKeyCode(e.which);
            console.log('BOXKeyUp()');
            if(nCode!==0)
            {
                this.changeSelection(nCode);
            }
            //keyUpAcItem(boxID, sel);
        });
        var s1;
        var nMax;
        var n;
        s1 = $(this.ac_box_id).attr(AutoCompleteIFD.cAttrScrollHeight);
        if(s1 && s1.length>0)
            nMax = Number(s1);
        else
            nMax = 50;
        s1 = $(this.ac_box_id).css( AutoCompleteIFD.cAttrHeight );
        if (s1)
            n = Number(s1.replace(AutoCompleteIFD.cPx,AutoCompleteIFD.cEmptyString));
        else
            n = 100500;
        if(n>nMax)
        {
            $(this.ac_box_id).css( AutoCompleteIFD.cAttrHeight, nMax.toString() + AutoCompleteIFD.cPx );
            $(this.ac_box_id).css( AutoCompleteIFD.cAttrOverflow, "scroll");
        }
        else
        {
            $(this.ac_box_id).css( AutoCompleteIFD.cAttrOverflow, "auto");
        }
        if(bShow===true)
            $(this.ac_box_id).show();
        console.log('leaving parseResponse()');
    }
    /**
     * query autocomplete items, set key handlers
     * @param {string} itemID - autocomplete text box id
     */
    queryAcItems(itemID)
    {
        var itemSel = AutoCompleteIFD.cSharp+itemID;
        var query_src = $(itemSel).attr("ac_src");
        var query_str = AutoCompleteIFD.cEmptyString;
        var param_str = $(itemSel).attr("ac_params");
        var params = param_str.split(";");
        var i;
        var pa;
        var pv;
        var param_sep = "&";
        console.log('entering queryAcItems('+itemSel+','+param_str+ ")");
        // combine query string
        for(i=0; i<params.length; i++)
        {
            if(params[i].length<1)
                continue;
            try
            {
                pa = params[i].split("=");
                if(pa[1].indexOf(AutoCompleteIFD.cSharp)===0)
                {
                    pv = $(pa[1]).val();
                }
                else
                {
                    if(pa[1].length>0)
                        pv = pa[1];
                    else
                        pv = $(itemSel).val();
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
                console.error(err);
                pv = AutoCompleteIFD.cEmptyString;
            }
        }
        var d = new Date();
        query_str += param_sep;
        query_str += "d=" + d.toString();
        console.log('params parsed: ' + query_str);
        console.log('query to: ' + query_src);
        var pThis = this;
        // got query string - send request
        this.ac_jqxhr =  $.ajax({
            type: "POST",
            url: query_src,
            cache: false,
            dataType: "json",
            data: query_str,
            success: function(jsonData, textStatus, jqXHR){
                console.log(AutoCompleteIFD.cSuccess);
                pThis.parseResponse(jsonData);
            }
        });
        console.log('leaving queryAcItems()');
    }
    /**
     * cancel item request (if any)
     * @returns {undefined}
     */
    cancelQuery()
    {
        try {
            if(this.ac_jqxhr)
                this.ac_jqxhr.abort();
            this.ac_jqxhr = null;    
        } catch (e) {
            this.ac_jqxhr = null;
        }
    }
    /**
     * hide autocomplete box, cancel request
     * @returns {undefined}
     */
    hideAcBox()
    {
        var textTargetID = $(this.ac_box_id).prop(AutoCompleteIFD.cAttrFor);
        $(this.ac_box_id).hide();
        this.cancelQuery();
        if(textTargetID)
        {
            var vExit = 10;
            var vi = -1;
            var ctl = $(AutoCompleteIFD.cDialogBoxText).find(AutoCompleteIFD.cFormCtlSel);
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
            document.getElementById(this.ac_box).setAttribute(AutoCompleteIFD.cAttrFor, AutoCompleteIFD.cEmptyString);
        }
        else
        {
            console.log('No target in ' + this.ac_box_id);
        }

    }
    /**
     * display autocomplete box under item
     * @param {string} itemID - item under div will be displayed
     * @returns {undefined}
     */
    displayAcBox(itemID)
    {
        var itemSel = AutoCompleteIFD.cSharp + itemID;
        if(itemSel.length>2 && // something to find
                (!($(this.ac_box_id).is(AutoCompleteIFD.cVisibleSel)) || // box not visible
                $(this.ac_box_id).attr(AutoCompleteIFD.cAttrFor)!==itemID)) // box not under item
        {
            var p;
            if($(itemSel).hasClass( AutoCompleteIFD.cFormControl ))
                p = $(itemSel).position();
            else
                p = $(itemSel).offset();
            if(p)
            {
                console.log('item:'+ itemID + ', top:' + p.top.toString() + ', left:' + p.left.toString());
                var s1 = $(itemSel).css( AutoCompleteIFD.cAttrHeight );
                var n1 = 0;
                n1 = Number(s1.replace(AutoCompleteIFD.cPx,AutoCompleteIFD.cEmptyString));
                n1 += n1;
                n1 += Number(p.top);
                p.top =  n1 ;
    //            $("#ac").offset({ top: p.top, left: p.left});
                $(this.ac_box_id).offset({ top: p.top, left: p.left});
                console.log('box:'+ this.ac_box + ', top:' + p.top.toString() + ', left:' + p.left.toString());
                $(this.ac_box_id).css(AutoCompleteIFD.cAttrTop, p.top);
                $(this.ac_box_id).css(AutoCompleteIFD.cAttrLeft, p.left);
                s1 = $(itemSel).css( AutoCompleteIFD.cAttrWidth );
                $(this.ac_box_id).css(AutoCompleteIFD.cAttrWidth, s1);
                $(this.ac_box_id).show();
                $(this.ac_box_id).attr(AutoCompleteIFD.cAttrFor, itemID);
                $(this.ac_box_id).attr(AutoCompleteIFD.cSelectedACItem, AutoCompleteIFD.cEmptyString);
            }
            else
            {
                console.log('no offset nor position defined');
            }
        }
        this.queryAcItems(itemID);
    }

    textFieldKeyUp(event)
    {
       var nCode = this.translateKeyCode(event.which);
       var itemID;
       var itemText;
       console.log('entering textFieldKeyUp(' + nCode.toString() + ')');
       if(nCode===-4 || nCode===5)
       {
           this.hideAcBox();
       }
       if(nCode===-4)
       {
           event.preventDefault();
       }
       switch(nCode)
       {
           case 0:
               if(event.currentTarget.value.length>2)
               {
                   this.displayAcBox(event.currentTarget.id);
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
               if(!this.changeSelection(nCode))
               {
                   event.preventDefault();
               }
               break;
           case 4:
               itemID = $(this.ac_box_id).attr(AutoCompleteIFD.cSelectedACItem);
               if(itemID && itemID.length>0)
               {
                   itemText = $(AutoCompleteIFD.cSharp+itemID).text();
                   if(itemText)
                   {
                       var sel_item_id = $(this.ac_box_id).attr(AutoCompleteIFD.cAttrFor);
                       this.selectAcItem(itemID, itemText);
                       this.selectNextInput(AutoCompleteIFD.cFormCtlSel, sel_item_id, AutoCompleteIFD.cOKBtnSel);
                   }
               }
       }
       console.log('leaving textFieldKeyUp()');
       return true;
   }

}

Object.defineProperty(AutoCompleteIFD, 'cSuccess', {
    value: 'success',
    writable: false,
    enumerable: true,
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cUndefinedStr', {
    value: 'undefined',
    writable: false,
    enumerable: true,
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cSharp', {
    value: '#',
    writable: false,
    enumerable: true,
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cEmptyString', {
    value: '',
    writable: false,
    enumerable: true,
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cPx', {
    value: 'px',
    writable: false,
    enumerable: true,
    configurable: false
});
// direct styles	
Object.defineProperty(AutoCompleteIFD, 'cFormControl', {
    value: 'form-control', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cNotFound', {
    value: 'not_found', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAutoCILink', {
    value: 'ac_link', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAutoCIBordered', {
    value: 'ac_bordered', 
    writable: false, 
    enumerable: true, 
    configurable: false
});

// attributes	
Object.defineProperty(AutoCompleteIFD, 'cSelectedACItem', {
    value: 'selected_ac_item', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrFor', {
    value: 'for', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrHeight', {
    value: 'height', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrScrollHeight', {
    value: 'scroll_height', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrOverflow', {
    value: 'overflow', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrTop', {
    value: 'top', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrLeft', {
    value: 'left', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrWidth', {
    value: 'width', 
    writable: false, 
    enumerable: true, 
    configurable: false
});

// selectors	
Object.defineProperty(AutoCompleteIFD, 'cOKBtnSel', {
    value: '#OK_BTN', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cFormCtlSel', {
    value: '.form-control', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cDialogBoxText', {
    value: '#dlg_box_text', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cVisibleSel', {
    value: ':visible', 
    writable: false, 
    enumerable: true, 
    configurable: false
});

// bound configuration	
Object.defineProperty(AutoCompleteIFD, 'cAttrBoundID', {
    value: 'bound_id', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrBoundDest', {
    value: 'bound_dest', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrBoundNodeT', {
    value: 'bound_node_type', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrBoundNodeC', {
    value: 'bound_node_class', 
    writable: false, 
    enumerable: true, 
    configurable: false
});
Object.defineProperty(AutoCompleteIFD, 'cAttrBoundNodeN', {
    value: 'bound_node_name', 
    writable: false, 
    enumerable: true, 
    configurable: false
});

// autocomplete item prefix	
Object.defineProperty(AutoCompleteIFD, 'cAutoCItem', {
    value: 'aci_', 
    writable: false, 
    enumerable: true, 
    configurable: false
});

