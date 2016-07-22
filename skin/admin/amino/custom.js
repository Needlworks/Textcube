//<![CDATA[
link = document.createElement("link");
link.href = servicePath+"/resources/image/FontAwesome/css/font-awesome.min.css";
link.type = "text/css";
link.rel = "stylesheet";
link.media = "screen,print";

document.getElementsByTagName("head")[0].appendChild(link);

link = document.createElement("link");
link.href = servicePath+adminSkin+"/menu_icons.css";
link.type = "text/css";
link.rel = "stylesheet";
link.media = "screen,print";

document.getElementsByTagName("head")[0].appendChild(link);
//]]>