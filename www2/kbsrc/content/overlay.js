function KBSRC() {}
KBSRC.prototype = {
	BRCMaxItem: 50,
	hosts: false,
	timer : function() {
		var host, now = (new Date()).getTime();
		for (host in this.hosts) {
			var oHost = this.hosts[host];
			if (!oHost) continue;
			if (now - oHost.lastSync > 600000) {
				oHost.lastSync = now;
				oHost.sync();
			}
		}
	},
	dumpInfo: function() {
		var str = "dirty BRC as follows:\n";
		var bid, host;
		for (host in this.hosts) {
			var oHost = this.hosts[host];
			if (!oHost) continue;
			str += "\t" + oHost.host + ":\n";
			for(bid in oHost.dirty) {
				if (oHost.dirty[bid]) {
					var lst = oHost.rc[bid];
					str += "\t\t" + bid + ": ";
					for (j=0; j<kbsrc.BRCMaxItem; j++) {
						if (lst[j] == 0) break;
						if (j!=0) str += ",";
						str += lst[j];
					}
					str += "\n";
				}
			}
		}
		kbsrc.debugOut(str);
	},
	setStatus: function(host) {
		var active = host && kbsrc.hosts[host];
		if (active) {
			var txt, img;
			const oHost = kbsrc.hosts[host];
			if (oHost.status == 1) {
				txt = "KBSRC Activated (synchronizing)"
				img = "chrome://kbsrc/skin/kbsrcTransfer.gif";
			} else {
				var minutes = Math.floor(((new Date()).getTime() - oHost.lastSync) / 60000);
				txt = "KBSRC Activated (last sync: " + minutes + " minutes before)";
				img = "chrome://kbsrc/skin/kbsrcEnabled.gif";
			}
			document.getElementById('kbsrc-tooltip-value').setAttribute("value", txt);
			document.getElementById('kbsrc-status-image').setAttribute("src", img);
		}
		document.getElementById('kbsrc-status').setAttribute("collapsed", !active);
		//kbsrc.debugOut("status set: " + host);
	},
	debugOut: function(str, oHost) {
		var now = new Date();
		var d = now.getHours() + ":" + now.getMinutes() + ":" + now.getSeconds();
		var d1 = "";
		if (oHost) d1 = oHost.host + "(" + oHost.userid + ") ";
		dump("KBSRC: [" + d + "] " + d1 + str + "\n");
	},
	init : function() {
		var browser = gBrowser;
		browser.addEventListener("DOMContentLoaded", kbsrcPageLoadedHandler, true);
		browser.addEventListener("select", kbsrcTabSelectedHandler, false);
		browser.addEventListener("pageshow", kbsrcPageShowHandler, false);

		this.hWatcher = new kbsrcHTTPHeaderWatcher();
		var observerService = Components.classes["@mozilla.org/observer-service;1"].getService(Components.interfaces.nsIObserverService);
		observerService.addObserver(this.hWatcher, "http-on-examine-response", false);
		
		this.hosts = new Object();
		
		var self = this;
		this.hTimer = setInterval(function() {
			self.timer.call(self);
		}, 1000);
		
		kbsrc.debugOut("Loaded OK.");
	},
	deinit : function() {
		var browser = gBrowser;
		browser.removeEventListener("DOMContentLoaded", kbsrcPageLoadedHandler, true);
		browser.removeEventListener("select", kbsrcTabSelectedHandler, false);
		browser.removeEventListener("pageshow", kbsrcPageShowHandler, false);

		var observerService = Components.classes["@mozilla.org/observer-service;1"].getService(Components.interfaces.nsIObserverService);
		observerService.removeObserver(this.hWatcher, "http-on-examine-response", false);

		this.hosts = false;
		
		clearInterval(this.hTimer);
		
		kbsrc.debugOut("Unloaded OK.");
	}
}

function kbsrcTabSelectedHandler(event) {
	var n = event.originalTarget.localName;
	if (n == "tabs") kbsrcPageRefresh();
	else if (n == "tabpanels") kbsrc.setStatus(false);
}
function kbsrcPageShowHandler(event) {
	const doc = event.originalTarget;
	if(doc == gBrowser.contentDocument) kbsrcPageRefresh();
}
function kbsrcPageRefresh() {
	var doc = content.document;
	if(doc._kbsrc_haveChecked) kbsrc.setStatus(doc.location.host);
}
function kbsrcPageLoadedHandler(event) {
	const doc = event.originalTarget;
	if(doc instanceof HTMLDocument) {
		if(!doc._kbsrc_haveChecked) {
			doc._kbsrc_haveChecked = true;
			const protocol = doc.location.protocol;
			if(/^(?:https|http)\:$/.test(protocol)) {
				const host = doc.location.host;
				const oHost = kbsrc.hosts[host];
				if (oHost) {
					oHost.processDoc(doc);
					return;
				}
			}
		}
	}
	kbsrc.setStatus(false);
}

function kbsrcHTTPHeaderWatcher() {}
kbsrcHTTPHeaderWatcher.prototype = {
	observe: function(aSubject, aTopic, aData) {
		if (aTopic == 'http-on-examine-response') {
			aSubject.QueryInterface(Components.interfaces.nsIHttpChannel);
			this.onExamineResponse(aSubject);
		}
	},
	onExamineResponse : function (oHttp) {
		var userid = false, param = false;
		try {
			userid = oHttp.getResponseHeader("Set-KBSRC");
			param = oHttp.getResponseHeader("Set-Cookie");
		} catch(e) {}
		if (userid) {
			var host = oHttp.URI.host;
			var newHost = false;
			if (userid != "/" && param) {
				var pos = param.indexOf("WWWPARAMS=");
				if (pos != -1) {
					newHost = parseInt(param.substr(pos + 10));
					newHost = newHost & 0x1000;
				}
			}
			kbsrc.hosts[host] = newHost ? (new kbsrcHost(host, userid)) : false;
			kbsrc.setStatus(host);
		}
	}
};

var kbsrc = new KBSRC();
window.addEventListener("load", function() { kbsrc.init.call(kbsrc) }, false); 
window.addEventListener("unload", function() { kbsrc.deinit.call(kbsrc) }, false); 