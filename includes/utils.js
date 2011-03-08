function proxyfun() {

	if(document.newsletters.proxyq.checked == 1) { // they want to connect with proxy
		document.newsletters.knownproxy.disabled = false;
		document.getElementById('injectmsg').innerHTML = '<span class="alert smallfont"> Please be patient!</span>';
	} else {
		document.newsletters.knownproxy.disabled = true;
		document.getElementById('injectmsg').innerHTML = '';
	}
}
