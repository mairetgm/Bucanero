define(["jquery"], function (jQuery) {
    "use strict";
    return {
        previewArea: null,
        currentAjaxRequest: null,
        initialize: function () {
            jQuery('#blackbox').draggable({
                handle: '.header',
                stop: function () {
                    this.savePosition();
                }.bind(this)
            }).bind(this);
            this.setPositionAndSize();
            $('blackbox').on('mouseup', function () {
                this.saveSize();
            }.bind(this));
            $('blackbox-size').observe('click', function () {
                this.switchSize();
            }.bind(this));
            $('blackbox-display').observe('click', function () {
                this.switchStatus();
            }.bind(this));
            $$(".updateOnChange").each(function (elt) {
                elt.observe("change", function () {
                    this.toggleNotification();
                }.bind(this))
            }.bind(this))
            $$('.blackbox-input').each(function (btn) {
                btn.observe('click', function () {
                    this.loadSource();
                }.bind(this));
            }.bind(this))
            $$('.blackbox-output').each(function (btn) {
                btn.observe('click', function () {
                    this.loadOutput();
                }.bind(this));
            }.bind(this))
            $$('.blackbox-library').each(function (btn) {
                btn.observe('click', function () {
                    this.loadLibrary();
                }.bind(this));
            }.bind(this))
            $('blackbox').setStyle({"display": "block"})
        },
        libraryLoaded: false,
        setCookie: function (c_name, value, exdays) {
            var exdate = new Date();
            exdate.setDate(exdate.getDate() + exdays);
            var c_value = encodeURI(value) + ((exdays === null) ? '' : '; expires=' + exdate.toUTCString());
            document.cookie = c_name + '=' + c_value + '; path=/;';
        },
        getCookie: function (c_name) {
            var c_value = document.cookie;
            var c_start = c_value.indexOf(' ' + c_name + '=');
            if (c_start === -1) {
                c_start = c_value.indexOf(c_name + '=');
            }
            if (c_start === -1) {
                c_value = null;
            } else {
                c_start = c_value.indexOf('=', c_start) + 1;
                var c_end = c_value.indexOf(';', c_start);
                if (c_end === -1) {
                    c_end = c_value.length;
                }
                c_value = decodeURI(c_value.substring(c_start, c_end));
            }
            return c_value;
        },
        savePosition: function () {
            var top = $('blackbox').getStyle('top').replace('px', '');
            var left = $('blackbox').getStyle('left').replace('px', '');
            var viewport = document.viewport.getDimensions();
            if (top < 0)
                top = 0;
            if (left < 0)
                left = 0;
            if (left > viewport.width - 20)
                left = viewport.width - 20;
            if (top > viewport.height - 20)
                top = viewport.height - 20;
            this.setCookie('blackbox.top', top);
            this.setCookie('blackbox.left', left);
        },
        saveSize: function () {
            var dimensions = $$('#blackbox .content')[0].getDimensions();
            var width = dimensions.width;
            var height = dimensions.height;
            this.setCookie('blackbox.width', width);
            this.setCookie('blackbox.height', height);
//        this.previewArea.refresh();
        },
        setPositionAndSize: function () {
            var top = this.getCookie('blackbox.top');
            var left = this.getCookie('blackbox.left');
            var width = this.getCookie('blackbox.width');
            var height = this.getCookie('blackbox.height');
            if (top === null) {
                top = 250;
            }
            if (left === null) {
                left = 900;
            }
            if (width === null) {
                width = 460;
            }
            if (height === null) {
                height = 360;
            }

//        $('blackbox').setStyle({
//            width: width + 'px',
//            height: height + 'px'
//        });

            $$('#blackbox .content')[0].setStyle({
                width: width + 'px',
                height: height + 'px'
            });
            $$('#blackbox.draggable')[0].setStyle({
                top: top + 'px',
                left: left + 'px',
                display: 'block'
            });
        },
        storage: {
            top: null,
            left: null,
            width: null,
            height: null
        },
        toggleButton: function (button) {
            var icons = button.childElements();
            icons.each(function (node) {
                node.toggleClassName('hide');
            });
        },
        switchStatus: function () {


            this.toggleButton($('blackbox-display'));
            $('blackbox').toggleClassName('arr_up');
            if ($('blackbox').hasClassName('arr_up')) {
                this.storage.height = $('blackbox').getStyle('height');
                $$('#blackbox .content')[0].addClassName("hidden")
                $$('#blackbox .footer')[0].addClassName("hidden")

                $('blackbox').setStyle({
                    overflow: "hidden",
                    height: '35px'
                });
                $('blackbox').removeClassName('resizable');
            } else {
                $$('#blackbox .content')[0].removeClassName("hidden")
                $$('#blackbox .footer')[0].removeClassName("hidden")

                $('blackbox').setStyle({
                    overflow: "auto",
                    height: 'auto'
                });
                if (false === $('blackbox').hasClassName('full')) {
                    $('blackbox').addClassName('resizable');
                }
            }


        },
        switchSize: function () {

            this.toggleButton($('blackbox-size'));
            $('blackbox').toggleClassName('full');
            if ($('blackbox').hasClassName('full')) {
                this.storage.top = $('blackbox').getStyle('top');
                this.storage.left = $('blackbox').getStyle('left');
                this.storage.width = $$('#blackbox .content')[0].getStyle('width');
                this.storage.height = $$('#blackbox .content')[0].getStyle('height');
                $('blackbox').setStyle({
                    top: '10px',
                    left: '10px',
                })
                $$('#blackbox .content')[0].setStyle({
                    width: (document.viewport.getDimensions().width - 40) + 'px',
                    height: (document.viewport.getDimensions().height - 100) + 'px'
                });
                $('blackbox').removeClassName('resizable');
            } else {
                $('blackbox').setStyle({
                    top: this.storage.top,
                    left: this.storage.left,
                });
                $$('#blackbox .content')[0].setStyle({
                    width: this.storage.width,
                    height: this.storage.height
                });
                $('blackbox').addClassName('resizable');
            }


        },
        loadLibrary: function () {
            this.toggleLoader();
            new Ajax.Request(orderUpdaterLoadLibraryUrl, {
                parameters: this.getData(),
                method: 'post',
                showLoader: false,
                onError: function (response) {
                    this.toggleError(response.responseText)

                },
                onSuccess: function (response) {
                    $("blackbox-title").update($("name").getValue() + " - " + "Library")
                    response = response.responseText.evalJSON();
                    if (response.error != 'false') {
                        this.toggleError(response.message);
                    } else {

                        this.tooglePreview(response, false)
                    }
                    callback();
                }.bind(this)
            })
        },
        loadOutput: function (callback) {
            this.toggleLoader();
            $("blackbox-title").update($("name").getValue() + " - " + "Loading")

            new Ajax.Request(orderUpdaterLoadPreviewUrl, {
                parameters: this.getData(),
                method: 'post',
                showLoader: false,
                onError: function (response) {
                    this.toggleError(response.responseText)

                }.bind(this),
                onSuccess: function (response) {
                    $("blackbox-title").update($("name").getValue() + " - " + "Output Preview")
                    response = response.responseText.evalJSON();
                    if (response.error != 'false') {
                        this.toggleError(response.message);
                    } else {
                        this.toggleOutput(response.message);
                    }
                    callback();
                }.bind(this)
            })
        },
        loadSource: function (callback) {

            this.toggleLoader();
            $("blackbox-title").update($("name").getValue() + " - " + "Loading")
            new Ajax.Request(orderUpdaterLoadFileUrl, {
                parameters: this.getData(),
                method: 'post',

                onError: function (response) {
                    this.toggleError(response.responseText)

                }.bind(this),
                onSuccess: function (response) {

                    $("blackbox-title").update($("name").getValue() + " - " + "Source Preview")
                    response = response.responseText.evalJSON();

                    if (response.error != 'false') {

                        this.toggleError(response.message);
                    } else {

                        this.tooglePreview(response, true)
                    }
                    callback();
                }.bind(this)
            })
        },
        tooglePreview: function (data, source) {

            this.hideAllArea()

            var table = "<table cellspacing='0' cellpading='0' width='100%'><thead><tr>";
            if (!source) {
                table += "<th colspan='1'>Identifier</th>";
                data.header.each(function (head, i) {
                    if (i > 0) {

                        var tag = data.tag[i];
                        var colspan = 1
                        var x = i;
                        if (tag != "") {
                            while (data.tag[x + 1] == "" && x < data.header.length) {

                                colspan++;
                                x++;

                            }


                            table += "<th colspan='" + colspan + "'>" + tag + "</th>";

                        }
                    }
                })
                table += "</tr><tr>";
            }

            data.header.each(function (head, i) {
                if (i == $("identifier_offset").getValue() && source) {
                    var name = "<b style='color:red'>identifier</b>";
                    var variable = "identifier";
                } else {
                    var name = head;
                    var variable = head;
                }

                if (source) {
                    table += "<th>" + name + " <br><i> $cell[" + i + "] | $cell['" + variable + "']</i></th>"
                } else {

                    var color = data.color[i];
                    table += "<th style='background:" + color + "'>" + name + "</th>"
                }
            })

            table += "</tr></thead><tbody>";
            data.data.each(function (line) {
                table += "<tr>"
                line.each(function (value, i) {
                    var color = data.color[i];
                    if (typeof value == "string" && value.trim() == "") {
                        value = "<span class='empty'>(empty)</span>"
                    }
                    else if(typeof value == "object"){
                        value = "<span class='xml-node'>(xml node)</span>"
                    }
                    else if(value==="NULL"){
                        value = "<span class='null'>(NULL)</span>"
                    }
                    table += "<td style='background:" + color + "'>" + value + "</td>";
                })
                table += "</tr>"
            })
            table += "</tbody><table>";
            $('content-preview').toggleClassName('hide').update(table);

            // if (source) {
            //     require(["wyomind_OrderUpdater_mapping"], function (mapping) {
            //
            //         mapping.data = data.header;
            //
            //         mapping.updateSource();
            //
            //     })
            // }

        },
        toggleOutput: function (data, source) {
                this.hideAllArea()
                $('content-preview').toggleClassName('hide').update(data);
        },
        toggleNotification: function () {
            $("blackbox-title").update($("name").getValue() + " - " + "Notification")
            this.hideAllArea()
            $('content-notification').toggleClassName('hide');
        },
        toggleError: function (msg) {

            $("blackbox-title").update($("name").getValue() + " - " + "Error")
            this.hideAllArea()
            $('content-error').toggleClassName('hide').update(msg);
        },
        toggleLoader: function () {

            this.hideAllArea()
            $('content-loader').toggleClassName('hide');
        },
        hideAllArea: function () {
            $$('#blackbox .area').each(function (item) {
                item.addClassName('hide');
            });
        },
        setActiveButton: function (btn) {
            $$('#blackbox .button.active').each(function (activeBtn) {
                activeBtn.removeClassName('active');
            });
            $$('#blackbox .button.' + btn).each(function (inactiveBtn) {
                inactiveBtn.addClassName('active');
            });
        },
        getData: function () {

            if ($$('#identifier OPTION[value="' + $('identifier').value + '"]').length) {
                var label = $$('#identifier OPTION[value="' + $('identifier').value + '"]')[0].innerText
            } else {
                var label = "";
            }
            return {
                file_path: $('file_path').value,
                file_system_type: $('file_system_type').value,
                xml_xpath_to_order: $('xml_xpath_to_order').value,
                file_type: $('file_type').value,
                ftp_host: $('ftp_host').value,
                ftp_login: $('ftp_login').value,
                ftp_password: $('ftp_password').value,
                ftp_dir: $('ftp_dir').value,
                ftp_port: $('ftp_port').value,
                use_sftp: $('use_sftp').value,
                ftp_active: $('ftp_active').value,
                field_delimiter: $('field_delimiter').value,
                field_enclosure: $('field_enclosure').value,
                identifier: $('identifier').value,
                identifier_label: label,
                identifier_offset: $('identifier_offset').value,
                mapping: $('mapping').value,
                preserve_xml_column_mapping: $("preserve_xml_column_mapping").value,
                xml_column_mapping: $("xml_column_mapping").value,
                dropbox_token: $('dropbox_token').value,
                line_filter: $('line_filter').value,
                has_header: $('has_header').value,
                webservice_params: $('webservice_params').value,
                webservice_login: $('webservice_login').value,
                webservice_password: $('webservice_password').value,
                order_identifier: $('order_identification').value,
                rules: $('rules').value,
            }
        }
    }


})
