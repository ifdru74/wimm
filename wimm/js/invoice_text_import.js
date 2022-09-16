/* 
 */

/**
 * detects date and time and fills #sel_dtm
 * @param {type} str text to look into
 * @returns {nothing}
 */
function detectDateAndTime(str)
{
    let phRe = /(\d{2,4}.\d{2}.\d{2,4}\s+\d{2}.\d{2}(\:\d\d)?)/gm;
    let aVars = [];

    let oHtml="";
    let m;

    while ((m = phRe.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === phRe.lastIndex) {
            phRe.lastIndex++;
        }
        // The result can be accessed through the `m`-variable.
        m.forEach((match, groupIndex) => {
            //console.log(`Found match, group ${groupIndex}: ${match}`);
            if(groupIndex===1)   {
                let fLen = aVars.length;
                let gotThis = false;
                for (let i = 0; i < fLen; i++) {
                    if(aVars[i]===match)
                    {
                        gotThis = true;
                        break;
                    }
                }
                if(!gotThis)
                {
                    aVars.push(match);
                    let dtmText = match;
                    let dtmValue = match;
                    let pdRe = /^(\d{2,4}).(\d{2}).(\d{2,4})\s+(\d{2}.\d{2}(\:\d\d)?)$/;
                    let m2;
                    if ((m2 = pdRe.exec(dtmText)) !== null) {
                        // This is necessary to avoid infinite loops with zero-width matches
                        if (m2.index === pdRe.lastIndex) {
                            pdRe.lastIndex++;
                        }
                        // The result can be accessed through the `m`-variable.
                        let y;
                        let m4;
                        let d;
                        let t;
                        m2.forEach((match2, groupIndex) => {
                            if(groupIndex===1)// year?
                            {
                                y = match2;
                            }
                            if(groupIndex===2)// month
                            {
                                m4 = match2;
                            }
                            if(groupIndex===3)// day?
                            {
                                if(y.length<3)
                                {
                                    d = y;
                                    y = match2;
                                }
                                else
                                {
                                    d = match2;
                                }
                            }
                            if(groupIndex===4)// time
                            {
                                t = match2;
                                t = t.replace(/\s+$/g,'');
                                if(t.length<6)
                                {
                                    t += ":00";
                                }
                            }
                        });
                        if(y && m4 && d && t)
                        {
                            if(y.length<3)
                            {
                                y = '20'+y;
                            }
                            dtmValue = y + "-" + m4 + "-" + d + " " + t;
                        }
                        else
                        {
                            console.log(y + "-" + m4 + "-" + d + " " + t);
                        }
                    }
                    oHtml += "                                " +
                             "<option value='"+dtmValue+"'>" + dtmText +
                             "</option>\n";
                }
            }
        });
    }        
    document.getElementById('sel_dtm').innerHTML = oHtml;
}

/**
 * detects amount values (totals, sums and so on) and fills #sel_amount
 * @param {type} str text to look into
 * @returns {nothing}
 */
function detectAmount(str)
{
    let phRe = /(Итог.\:?)\s+((\d{1,3}[\t ]*){1,3}[\,\.]\d\d[^\.\,])/gim;
    let aVars = [];

    let oHtml="";
    let m;

    while ((m = phRe.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === phRe.lastIndex) {
            phRe.lastIndex++;
        }
        // The result can be accessed through the `m`-variable.
        let bNDS = false;
        m.forEach((match, groupIndex) => {
            console.log(`Found match, group ${groupIndex}: ${match}`);
            if(groupIndex===1)   {
                bNds = (match.indexOf('НДС')>=0);
                console.log("amount='"+match+"'");
            }
            if(groupIndex===2 && !bNDS)   {
                let fLen = aVars.length;
                let gotThis = false;
                let s1 = match;
                s1 = s1.replace(/\s/g,'');
                s1 = s1.replace(',','.');
                for (let i = 0; i < fLen; i++) {
                    if(aVars[i]===s1)
                    {
                        gotThis = true;
                        break;
                    }
                }
                if(!gotThis)
                {
                    aVars.push(s1);
                    s2 = match.replace(/^\s+/g,'');
                    s2 = s2.replace(/\s+$/g,'');
                    oHtml += "                                " +
                            "<option value='"+s1+"'>" + s2 + "</option>\n";
                }
                console.log("s1='"+s1+"', match='"+match+"'");
            }
        });
    }
    document.getElementById('sel_amount').innerHTML = oHtml;
}

/**
 * detects transaction's place/receiver and fills #sel_where
 * @param {type} str text to look into
 * @returns {nothing}
 */
function detectWhere(str)
{
    let phRe = /(ИНН(\s+)?([^\s]+)?\:?\s+\d+)/gm;
    let aVars = [];

    let oHtml="";
    let m;

    while ((m = phRe.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === phRe.lastIndex) {
            phRe.lastIndex++;
        }
        // The result can be accessed through the `m`-variable.
        m.forEach((match, groupIndex) => {
            //console.log(`Found match, group ${groupIndex}: ${match}`);
            if(groupIndex===1)   {
                let fLen = aVars.length;
                let gotThis = false;
                let s1 = match;
                s1 = s1.replace(/^ИНН(\s+)?([^\s]+)?\:?\s+/g,'ИНН:');
                for (let i = 0; i < fLen; i++) {
                    if(aVars[i]===s1)
                    {
                        gotThis = true;
                        break;
                    }
                }
                if(!gotThis)
                {
                    aVars.push(s1);
                    oHtml += "                                " +
                             "<option value='"+s1.substring(4)+"'>" + s1 + "</option>\n";
                    console.log("s1='"+s1+"', match='"+match+"'");
                }
            }
        });
    }
    document.getElementById('sel_where').innerHTML = oHtml;
}

/**
 * detects payment purpose and fills #sel_for
 * @param {type} str text to look into
 * @returns {nothing}
 */
function detectFor(str)
{
    let phRe = /(\d+\.\s+.+)/gm;
    let aVars = [];

    let oHtml="";
    let m;

    while ((m = phRe.exec(str)) !== null) {
        // This is necessary to avoid infinite loops with zero-width matches
        if (m.index === phRe.lastIndex) {
            phRe.lastIndex++;
        }
        // The result can be accessed through the `m`-variable.
        m.forEach((match, groupIndex) => {
            //console.log(`Found match, group ${groupIndex}: ${match}`);
            if(groupIndex===1)   {
                let fLen = aVars.length;
                let gotThis = false;
                let s1 = match;
                for (let i = 0; i < fLen; i++) {
                    if(aVars[i]===s1)
                    {
                        gotThis = true;
                        break;
                    }
                }
                if(!gotThis)
                {
                    aVars.push(s1);
                    oHtml += "                                " +
                             "<option value='"+s1+"'>" + s1 + "</option>\n";
                    console.log("s1='"+s1+"', match='"+match+"'");
                }
            }
        });
    }
    document.getElementById('sel_for').innerHTML = oHtml;
}

/**
 * runs all the detector functions from below and catch exception
 * @returns {nothing}
 */
function detectFields()
{
    try
    {
        const str = document.getElementById('txt2Import').value;
        detectDateAndTime(str);
        detectAmount(str);
        detectWhere(str);
        detectFor(str);
    } catch (e) {
        console.log(e.toString());
    }
}

/**
 * gets and returns value of the passed element. 
 * if exception happen returns uninitialized value
 * @param {string} selID
 * @returns {string} of {undefined}
 */
function getSelectedOptionT(selID)
{
    let opt;
    try
    {
        let selObj = document.getElementById(selID);
        let si = selObj.selectedIndex;
        opt = selObj.options.item(si);
    } catch (e) {
        console.log(e.toString());
    }
    return opt;
}

/**
 * parse place query responce
 * @param {type} jsonData
 * @param {string} textStatus
 * @param {type} jqXHR
 * @param {string} txtID id of the element which will be updated in the case of
 *                       success
 * @returns {nothing}
 */
function    parsePlaceResponse(jsonData, textStatus, jqXHR, txtID)
{
    var callChange = false;
    if(!jsonData)
    {
        console.log('entering parsePlaceResponse() - null result');
        return ;
    }
    var arr = jsonData;
    var i;
    var item;
    var sel_item_id;
    var item_text = '';
    var html = '';
    var o;
    var s1;
    var bShow = true;
    o = document.getElementById(txtID);
    if(o)
    {
        if(arr.length>=1)
        {
            if(arr[0] && arr[0].id && arr[0].text)
            {
                if(arr[0].id!=='error')
                {
                    o.value = arr[0].id;
                    const hide_item_id = o.getAttribute('focus_on');
                    let t = document.getElementById(hide_item_id);
                    t.value = arr[0].text;
                }
                else
                {
                    console.log('parsePlaceResponse("'+arr[0].text+'")');
                }
            }
            else
            {
                console.log('parsePlaceResponse("Bad element")');
            }
        }
        else
        {
            console.log('parsePlaceResponse("Empty array")');
        }
    }
    else
    {
        console.log('parsePlaceResponse("Bad HTML element id")');
    }
}        

/**
 * transfer selected data into main form, issues a place lookup request
 * @param {string} mainDialogID dialog to activate
 * @param {string} importDialogID dialog to passivate
 * @returns {nothing}
 */
function transferData(mainDialogID, importDialogID, acUrl)
{
    let opt = getSelectedOptionT('sel_amount');
    if(opt)
    {
        if(opt.value)
        {
            document.getElementById("t_sum").value = opt.value;
        }
        else
        {
            document.getElementById("t_sum").value = opt.text;
        }
    }
    opt = getSelectedOptionT('sel_dtm');
    if(opt)
    {
        if(opt.value)
        {
            document.getElementById("t_date").value = opt.value;
        }
        else
        {
            document.getElementById("t_date").value = opt.text;
        }
    }
    opt = getSelectedOptionT('sel_for');
    if(opt)
    {
        document.getElementById("t_name").value = opt.text;
    }
    opt = getSelectedOptionT('sel_where');
    if(opt)
    {
        if(opt.value)
        {
            //const acUrl="<?php echo get_autocomplete_url();?>";
            let acParams = "type=t_place_inn&ac_filter=" + opt.value;
            let d = new Date();
            const param_sep = "&";
            acParams += param_sep;
            acParams += "d=" + d.toString();
            console.log('params parsed: ' + acParams);
            console.log('query to: ' + acUrl);
            // got query string - send request
            ac_jqxhr =  $.ajax({
                type: "POST",
                url: acUrl,
                cache: false,
                dataType: "json",
                data: acParams,
                success: function(jsonData, textStatus, jqXHR){
                    parsePlaceResponse(jsonData, textStatus, jqXHR, 
                        "t_place");
                }
            });
        }
    }
    if(!mainDialogID)
    {
        mainDialogID = "#dialog_box";
    }
    if(!importDialogID)
    {
        importDialogID = "#import_box";
    }
    $("#import_box").modal('hide');
    doCancelImport(mainDialogID);
}

/**
 * opens import dialog
 * @param {string} mainDialogID dialog to passivate
 * @param {string} importDialogID dialog to activate
 * @returns {nothing}
 */
function openImport(mainDialogID, importDialogID)
{
    if(!mainDialogID)
    {
        mainDialogID = "#dialog_box";
    }
    if(!importDialogID)
    {
        importDialogID = "#import_box";
    }
    $(mainDialogID).removeClass("modal");
    $(importDialogID).modal('show');
}

/**
 * returns proper class to main dialog
 * @param {type} mainDialogID dialog to activate
 * @returns {nothing}
 */
function doCancelImport(mainDialogID)
{
    if(!mainDialogID)
    {
        mainDialogID = "#dialog_box";
    }
    $(mainDialogID).addClass("modal");
}
