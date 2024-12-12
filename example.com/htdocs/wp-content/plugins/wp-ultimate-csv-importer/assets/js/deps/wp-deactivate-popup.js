
function showSubmit(){
    var reason = document.getElementsByName('selected-reason')
    for(i = 0; i < reason.length; i++) { 
        if(reason[i].checked){
            if(reason[i].value == 1){
                document.getElementById('input-reason2').style.display="none" 
                document.getElementById('input-reason5').style.display="none" 
            }else if(reason[i].value == 2){
                document.getElementById('input-reason2').style.display="block"
                document.getElementById('input-reason2').focus();
                document.getElementById('input-reason5').style.display="none"
            }else if(reason[i].value == 3){
                document.getElementById('input-reason2').style.display="none" 
                document.getElementById('input-reason5').style.display="none" 
            }else if(reason[i].value == 4){
                document.getElementById('input-reason2').style.display="none" 
                document.getElementById('input-reason5').style.display="none" 
            }else if(reason[i].value == 5){
                document.getElementById('input-reason5').style.display="block"
                document.getElementById('input-reason5').focus();
                document.getElementById('input-reason2').style.display="none" 
            }
        }
    }
    
    document.getElementById('skipanddeactivate').style.display="none";
    document.getElementById('mailsubmit').setAttribute('style','margin-right:10px;');
}
function hideSubmit(){
    document.getElementById('mailsubmit').style.display="none";
    document.getElementById('skipanddeactivate').setAttribute('style','margin-right:10px;');
}
function submitReason(){
    var mailReason = ""
    var reason = document.getElementsByName('selected-reason')
    for(i = 0; i < reason.length; i++) { 
        if(reason[i].checked){
            if(reason[i].value == 1){
                mailReason = "The plugin did not work"
            }else if(reason[i].value == 2){
                mailReason = "I found a better plugin.\n"
                var iReason = document.getElementById('input-reason2');
                if(iReason.value == ""){
                    alert("Please Enter the plugin name!")
                    iReason.focus();
                    return false;
                }else{
                    mailReason += iReason.value;                    
                }
            }else if(reason[i].value == 3){
                mailReason = "I do not like to share my information with you"
            }else if(reason[i].value == 4){
                mailReason = "It is a temporary deactivation. I am just debugging an issue"
            }else if(reason[i].value == 5){
                mailReason = "Reason type : Other.\n"
                var iReason = document.getElementById('input-reason5');
                if(iReason.value == ""){
                    alert("Please Enter the reason to deactivate this plugin!")
                    iReason.focus();
                    return false;
                }else{
                    mailReason += iReason.value;                    
                }
            }
        }
    } 
    document.getElementById('myModal').style.display='none'
    document.getElementById("loader").style.display = 'block';
    var formData = new FormData();
    formData.append('action', 'DeactivateMail');
    formData.append('reason', mailReason);
    formData.append("securekey", window.smack_nonce_object.nonce);
    var xhttp = new XMLHttpRequest();
    xhttp.open("POST",ajaxurl,true);
    xhttp.send(formData);    
    xhttp.onreadystatechange = function() {
        if (xhttp.readyState === 4) {
            document.getElementById("skipanddeactivate").click();
            document.getElementById("loader").style.display = 'none';
        }
    }
    return true;
}
window.onload = function(){
    var popUp = document.querySelector('[data-slug="wp-ultimate-csv-importer"] .deactivate a');
    if (typeof(popUp) != 'undefined' && popUp != null){
        var urlRedirect = popUp.getAttribute('href');
        popUp.onclick = function(event){
            event.preventDefault()
            var removeModal = document.getElementById("myModal");
            if (typeof(removeModal) != 'undefined' && removeModal != null){
            }else{
                popupModal();
            }
            document.getElementById('myModal').style.display='block'
        }
    }  
    function popupModal(){
        var loaderdiv = document.createElement('div');
        loaderdiv.setAttribute('id','loader');
        document.body.appendChild(loaderdiv);

        var maindiv = document.createElement('div');
        maindiv.setAttribute('id','myModal');
        maindiv.setAttribute('class','myModal');
        document.body.appendChild(maindiv);

        var modalContent = document.createElement("div");
        modalContent.setAttribute('class','modal-content');
        maindiv.appendChild(modalContent);

        var header = document.createElement("div")
        header.setAttribute('style','display:flex;align-items:center;justify-content:space-between;')

        var headerhead = document.createElement("h3");
        var headerheadS = document.createElement("strong");
        headerheadS.innerHTML = "Quick Feedback";

        headerhead.appendChild(headerheadS);
        header.appendChild(headerhead);

        var close = document.createElement("span");
        close.setAttribute('class',"close-button");
        close.setAttribute('onclick',"document.getElementById('myModal').style.display='none'")
        close.innerHTML = "×";
        header.appendChild(close);

        modalContent.appendChild(header);

        var body = document.createElement('div');
        body.setAttribute('class','card cardView');
        modalContent.appendChild(body);

        var bodyhead = document.createElement("h4");
        var bodyheadS = document.createElement("strong");
        bodyheadS.innerHTML = "If you have a moment, please let us know why you are deactivating:";

        bodyhead.appendChild(bodyheadS);
        body.appendChild(bodyhead);

        var ul = document.createElement("ul");
        body.appendChild(ul);

        var li = [];
        var br = [];
        var label = [];
        var span = [];
        var input = [];
        var inputReason = [];
        var spanContent = [];
        for (i = 1; i <= 5; i++) {
            li[i] = document.createElement("li");
            ul.appendChild(li[i]);

            label[i] = document.createElement("label");
            li[i].appendChild(label[i]);

            span[i] = document.createElement("span");
            label[i].appendChild(span[i]);
            
            input[i] = document.createElement("input");
            input[i].setAttribute('type','radio');
            input[i].setAttribute('name','selected-reason');
            input[i].setAttribute('id','selected-reason'+i);
            input[i].setAttribute('value',i);
            input[i].setAttribute('onchange','showSubmit()');
            span[i].appendChild(input[i]);

            br[i] = document.createElement("br");
            li[i].appendChild(br[i]);

            inputReason[i] = document.createElement("input");
            inputReason[i].setAttribute('type','text');
            inputReason[i].setAttribute('id','input-reason'+i);
            inputReason[i].setAttribute('name','input-reason'+i);
            inputReason[i].setAttribute('style','display:none;margin-left:20px;margin-top:10px;margin-bottom:5px;width:70%;');
            li[i].appendChild(inputReason[i]);

            spanContent[i] = document.createElement("span");
            label[i].appendChild(spanContent[i]);
        }

        document.getElementById('input-reason2').setAttribute('placeholder',"What's the plugin's name?")
        document.getElementById('input-reason5').setAttribute('placeholder',"Kindly tell us the reason so we can improve?")

        spanContent[1].innerHTML = "The plugin didn't work";
        spanContent[2].innerHTML = "I found a better plugin";
        spanContent[3].innerHTML = "I don't like to share my information with you";
        spanContent[4].innerHTML = "It's a temporary deactivation. I'm just debugging an issue";
        spanContent[5].innerHTML = "Other";

        var footer = document.createElement("div");
        footer.setAttribute('style','float:right');
        modalContent.appendChild(footer);

        var submitBtn = document.createElement('input');
        submitBtn.setAttribute('type','button');
        submitBtn.setAttribute('id','mailsubmit');
        submitBtn.setAttribute('name','mailsubmit');
        submitBtn.setAttribute('class','button button-secondary button-deactivate allow-deactivate');
        submitBtn.setAttribute('value','Submit & Deactivate');
        submitBtn.setAttribute('onclick','submitReason()')
        submitBtn.setAttribute('style','display:none;margin-right:10px;');
        footer.appendChild(submitBtn);

        var skipBtn = document.createElement('a');
        skipBtn.setAttribute('id','skipanddeactivate');
        skipBtn.setAttribute('class','button button-secondary button-deactivate allow-deactivate');
        skipBtn.setAttribute('style','margin-right:10px;');
        skipBtn.setAttribute('href',urlRedirect)
        skipBtn.innerHTML = "Skip &amp; Deactivate";
        footer.appendChild(skipBtn);

        var cancelBtn = document.createElement('span');
        cancelBtn.setAttribute('class','button button-secondary button-close');
        cancelBtn.setAttribute('onclick',"document.getElementById('myModal').style.display='none'");
        cancelBtn.innerHTML = "Cancel";
        footer.appendChild(cancelBtn); 
    }
}