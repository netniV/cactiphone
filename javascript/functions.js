var iWebkit;

if (!iWebkit) {
	
	iWebkit = window.onload = function () {
			function fullscreen() {
				var a = document.getElementsByTagName("a");
				for (var i = 0; i < a.length;i++) {
					if (a[i].className.match("noeffect")) {
					}
				else {
						a[i].onclick = function () {
							window.location = this.getAttribute("href");
							return false;
						};
					}
				}
			}

			function hideURLbar() {
				window.scrollTo(0, 0.9);
			}
			iWebkit.init = function () {
				fullscreen();
				hideURLbar();
			};
			iWebkit.init();
		};
}

function getURLParameter(name) {
  return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
}

function timedRefresh(timeoutPeriod, countdownstring) {
   var timer = setInterval(function() {
   if (timeoutPeriod > 1) {
		timeoutPeriod -=1;
		document.getElementById("countdown").innerHTML = countdownstring.replace('<countdown>',timeoutPeriod);
   } else {
       clearInterval(timer);
            window.location.href = window.location.href;
       };
   }, 1000);
}