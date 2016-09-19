(function () {

    var URL = '/javascript/ueditor/';
    window.UEDITOR_CONFIG = {
        UEDITOR_HOME_URL: URL
        , serverUrl: "/Uploader/"
        , toolbars: [["source","undo","redo","fontfamily","fontsize","bold","italic","underline","fontborder","strikethrough","forecolor","insertunorderedlist","insertorderedlist","backcolor","superscript","subscript","justifyleft","justifycenter","justifyright","justifyjustify","indent","unlink","link","searchreplace","pagebreak","simpleupload","insertimage","music","emotion","insertvideo","attachment","horizontal","blockquote","spechars"],[]]
        ,lang:'zh-cn'
        ,langPath:URL +"lang/"
        ,zIndex : 10000
        ,charset:"utf-8"
        ,isShow : true
        ,initialFrameWidth:650
        ,initialFrameHeight:250
        ,sourceEditor:'textarea'
    };

    function getUEBasePath(docUrl, confUrl) {

        return getBasePath(docUrl || self.document.URL || self.location.href, confUrl || getConfigFilePath());

    }

    function getConfigFilePath() {

        var configPath = document.getElementsByTagName('script');

        return configPath[ configPath.length - 1 ].src;

    }

    function getBasePath(docUrl, confUrl) {

        var basePath = confUrl;


        if (/^(\/|\\\\)/.test(confUrl)) {

            basePath = /^.+?\w(\/|\\\\)/.exec(docUrl)[0] + confUrl.replace(/^(\/|\\\\)/, '');

        } else if (!/^[a-z]+:/i.test(confUrl)) {

            docUrl = docUrl.split("#")[0].split("?")[0].replace(/[^\\\/]+$/, '');

            basePath = docUrl + "" + confUrl;

        }

        return optimizationPath(basePath);

    }

    function optimizationPath(path) {

        var protocol = /^[a-z]+:\/\//.exec(path)[ 0 ],
            tmp = null,
            res = [];

        path = path.replace(protocol, "").split("?")[0].split("#")[0];

        path = path.replace(/\\/g, '/').split(/\//);

        path[ path.length - 1 ] = "";

        while (path.length) {

            if (( tmp = path.shift() ) === "..") {
                res.pop();
            } else if (tmp !== ".") {
                res.push(tmp);
            }

        }

        return protocol + res.join("/");

    }

    window.UE = {
        getUEBasePath: getUEBasePath
    };

})();
