/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
define(['jquery',
    'underscore',
    'mage/template',
    "Wyomind_Framework/js/codemirror5/lib/codemirror",
    "Wyomind_Framework/js/codemirror5/addon/selection/active-line",
    "Wyomind_Framework/js/codemirror5/addon/edit/matchbrackets",
    "Wyomind_Framework/js/codemirror5/mode/htmlmixed/htmlmixed",
    "Wyomind_Framework/js/codemirror5/mode/xml/xml",
    "Wyomind_Framework/js/codemirror5/mode/javascript/javascript",
    "Wyomind_Framework/js/codemirror5/mode/css/css",
    "Wyomind_Framework/js/codemirror5/mode/clike/clike",
    "Wyomind_Framework/js/codemirror5/mode/php/php",
    "Wyomind_Framework/js/codemirror5/addon/display/autorefresh",
    'jquery/ui',
    "jquery/colorpicker/js/colorpicker"
], function ($, _, mageTemplate, codeMirror) {
    'use strict';
    return {
        createCodeMirror: function (selector, mode) {
            return codeMirror.fromTextArea(selector, {
                mode: {
                    name: mode
                },
                lint: false,
                //  lineWrapping: true,
                styleActiveLine: true,
                matchBrackets: true,
                autoCloseBrackets: true,
                autoCloseTags: true,
                autoRefresh: true
            })
        },
        colorpicker: function () {
            $('.color').ColorPicker({
                    onShow: function (el) {
                        $(el).fadeIn(500);
                        return false;
                    },
                    onSubmit:
                        function (hsb, hex, rgb, el) {
                            $(el).parent().find(".acolor").val("rgba(" + rgb.r + "," + rgb.g + "," + rgb.b + ",0.5)").change(); // save the background color
                            $(el).ColorPickerHide();
                        }
                }
            );
        },
        data: {},
        actionOptionsNb: 3,
        initialize: function () {
            // @TODO mettre un flag pendant l'initialisation pour qu'il n'y ait pas de sauvegarde des rules pendant ce temps?
            var profilerenderer;
            profilerenderer = this;

            // Initialize codemirror for the XML custom structure
            var xml_column_mapping = profilerenderer.createCodeMirror(document.getElementById("xml_column_mapping"), "application/ld+json");
            function updateTextArea() {
                xml_column_mapping.save();
                toolbox.toggleNotification();
            }
            xml_column_mapping.on('change', updateTextArea);

            let rulesData;
            if ($("#identifier_offset").length > 0 && $("#identifier_offset option").length) {
                profilerenderer.activateObserver();
                rulesData = ($("#rules").val()) ? JSON.parse($("#rules").val()) : {0:''};
                _.each(rulesData, function(rule, ruleIndex) // A rule is a container for a couple conditions + actions
                {
                    let deletable;
                    deletable = (ruleIndex == 0) ? false : true;
                    profilerenderer.renderRule(rule, 'ou-rule-template', 'ou-condition-template', 'ou-action-template', $('#rules-area'), 'append', deletable);
                });

                // activate sortability
                profilerenderer.sortable();

                // activate colorpicker
                profilerenderer.colorpicker();
            } else {
                profilerenderer.renderNoFile('no-file-template', 'rules-area');
            }
        },
        renderNoFile: function (noFileUjsTemplate, outputDiv) {
            let noFileTemplate;
            noFileTemplate = mageTemplate("#"+noFileUjsTemplate, {});
            $("#"+outputDiv).html(noFileTemplate);
        },
        renderRule: function (ruleData, rulesUjsTemplate, conditionsUjsTemplate, actionsUjsTemplate, outputDiv, outputMethod, deletable) {
            let rulesData, i, ruleTemplate, insertedRule;
            var profilerenderer;
            profilerenderer = this;
            if (ruleData == '') {
                ruleData = {
                    conditions: {
                        0: {}
                    },
                    actions: {
                        0: {}
                    }
                }
            }

            // generate rule template
            ruleTemplate = mageTemplate("#"+rulesUjsTemplate, {
                "deletable": deletable
            });
            if (outputMethod == 'after') {
                $(outputDiv).after(ruleTemplate);
                insertedRule = $(outputDiv).next(".rule-row");
            } else {
                $(outputDiv).append(ruleTemplate);
                insertedRule = $(outputDiv).last(".rule-row");
            }

            // populate rule template
            if (ruleData.name != "") {
                $(".rtitle").last().val(ruleData.name);
                $(".rtitle").last().parents(".rule-title").removeClass("hidden");
                $(".tagrule").last().toggleClass("active");
            }

            if (ruleData.disabled == true) {
                $(".order_rule_disable .link").last().click();
            }

            if (Object.keys(ruleData.conditions).length == 0 // if rule has no active condition, print the no condition template
                || (Object.keys(ruleData.conditions).length ==1
                    && ($.isEmptyObject(ruleData.conditions[0])
                        || ruleData.conditions[0]['condition'] == 'all'
                    )
                )
            ) {
                profilerenderer.renderNoCondition('ou-nocondition-template', $(insertedRule).find(".conditions-title").last());
            } else {
                _.each(ruleData.conditions, function(condition, conditionIndex) {
                    if (condition.condition != "all") { // Only render condition if it has been inputted. Do not keep uninputted conditions
                        let deletable, targetDiv;
                        if (conditionIndex == 0) {
                            targetDiv = $(insertedRule).find(".conditions-title").last();
                            deletable = false;
                        } else {
                            targetDiv = $(insertedRule).find(".conditions-row").last();
                            deletable = true;
                        }
                        profilerenderer.renderCondition(conditionsUjsTemplate, targetDiv, 'after', deletable, condition);
                    }
                });
            }

            _.each(ruleData.actions, function(action, actionIndex) {
                let deletable, targetDiv, outputMethod;
                targetDiv = $(insertedRule).find(".actions-list").last();
                if (actionIndex == 0) {
                    deletable = false;
                } else {
                    deletable = true;
                }
                deletable = (actionIndex != 0);
                profilerenderer.renderAction(actionsUjsTemplate, targetDiv, 'append', deletable, action);
            });
        },
        renderNoCondition: function (noConditionsUjsTemplate, outputDiv) {
            var profilerenderer;
            profilerenderer = this;
            let conditionTemplate;

            conditionTemplate = mageTemplate("#"+noConditionsUjsTemplate, {
            });
            $(outputDiv).after(conditionTemplate);
        },
        renderCondition: function (conditionsUjsTemplate, outputDiv, outputMethod, deletable, conditionData) {
            var profilerenderer;
            profilerenderer = this;
            let conditionTemplate, conditionsCount;

            // remove nocondition element if it exists
            outputDiv.parents(".order_rule").find(".noconditions-row").remove();

            // if the condition data is not set and the current conditions are empty, render the 'no condition' text
            conditionsCount = outputDiv.parents(".order_rule").find(".conditions-row").length; // number of conditions in the current rule
            if (conditionsCount == 0) {
                deletable = false;
            }

            conditionTemplate = mageTemplate("#"+conditionsUjsTemplate, {
                "deletable": deletable,
                "operands": this.operands,
                "conditions": this.conditions,
                "conditionOperands": this.conditionOperands,
                "conditionId" : profilerenderer.getUniqueId()
            });
            if (outputMethod == 'after') {
                $(outputDiv).after(conditionTemplate);
            } else {
                $(outputDiv).append(conditionTemplate);
            }
            _.each(conditionData, function(value, key) { // Set the values for the added condition if they exist
                if (key == 'operand') {
                    if (value == 'and') {
                        $("."+key).last().attr('checked', true).change();
                    }
                } else
                {
                    $("."+key).last().val(value).change();
                }
            });
        },
        renderAction: function (actionsUjsTemplate, outputDiv, outputMethod, deletable, actionData) {
            let action;
            action = mageTemplate("#"+actionsUjsTemplate, {
                "deletable": deletable,
                "actions": this.actions
            });
            if (outputMethod == 'after') {
                $(outputDiv).after(action);
            } else {
                $(outputDiv).append(action);
            }
            _.each(actionData, function(value, key) { // Set the values for the added action if they exist
                if(key.indexOf('-script') !== -1 && value != "") { // manage code icon display
                    $("."+key).last().parents(".action-options").find(".code").addClass("active");
                }
                if(key == 'atitle' && value != "") { // manage action label visibility
                    $("."+key).last().parents(".action-title").removeClass("hidden");
                    $(".tag").last().toggleClass("active");
                }
                $("."+key).last().val(value).change();
            });
        },
        activateObserver: function () {
            var profilerenderer, actions;
            profilerenderer = this;
            actions = this.actions;

            // listen changes in the form
            $(document).on('change', '#orderupdater_rules :input,select', function() {
                profilerenderer.saveRules();
            });

            var previousConditionOption;
            $(document).on('focus', '.condition', function() {
                previousConditionOption = $(this).val();
            }).on('change', '.condition', function() {
                let row = $(this).parents(".conditions-row");
                profilerenderer.updateCondition(row, $(this));
                profilerenderer.updateConditionValue(row, 'condition', previousConditionOption);
                $(this).blur(); // remove focus to allow the previous action to be saved in case of consecutive changes
            });

            var previousConditionOperandOption;
            $(document).on('focus', '.condition-operand', function() {
                previousConditionOperandOption = $(this).val();
            }).on('change', '.condition-operand', function() {
                let row = $(this).parents(".conditions-row");
                profilerenderer.updateConditionOperand(row, $(this));
                profilerenderer.updateConditionValue(row, 'condition-operand', previousConditionOperandOption);
                $(this).blur(); // remove focus to allow the previous action to be saved in case of consecutive changes
            });

            $(document).on('change', '.actions-row .action', function() {
                let row, actionOptionsTemplates;
                var actionOptionsTemplatesIndex;
                row = $(this).parents(".actions-row");

                actionOptionsTemplatesIndex = 1;
                if (actions[$(this).val()] && actions[$(this).val()].elements) {
                    actionOptionsTemplates = actions[$(this).val()].elements.map( function(element) {
                        let actionOptionsTemplate;
                        actionOptionsTemplate = profilerenderer.getActionElement(element, actionOptionsTemplatesIndex);
                        actionOptionsTemplatesIndex++;
                        return actionOptionsTemplate;
                    });
                } else {
                    actionOptionsTemplates = '';
                }

                row.find(".action-options").remove(); // remove existing action options on action change

                _.each(actionOptionsTemplates, function(actionOptionsTemplate, index) // add the action options to the DOM
                {
                    row.find(row.find(".action-cell").last()).after(actionOptionsTemplate);
                    row.find(row.find(".action-cell").last()).change();
                });

            });

            $(document).on('change', '.acolor', function() { // observe a change on the action color inputs to set the actions' background color
                let selectedColor;
                selectedColor = $(this).parent().find(".acolor").val();
                if (selectedColor != '') {
                    $(this).parents(".action-content").css({"background-color": selectedColor}) // set the background color
                    $(this).parent().find(".color").addClass("active");
                }
            });

            var previousActionOption;
            $(document).on('focus', '[class*=action-option-]', function() {
                previousActionOption = $(this).val();
            }).on('change', '[class*=action-option-]', function() {
                let row;
                row = $(this).parents(".actions-row");

                if ($(this).val() == '_custom_') {
                    row.find("."+$(this).attr("class")+"-custom").show();
                } else if (previousActionOption == '_custom_') {
                    row.find("."+$(this).attr("class")+"-custom").hide();
                }
                $(this).blur(); // remove focus to allow the previous action to be saved in case of consecutive changes
            });

            // actions on custom code
            $(document).on('click', '.icon.code', function() {
                var row = $(this).parents(".action-options");
                profilerenderer.script.open(row, profilerenderer)
            });
            $(document).on('click', '#scripting .validate', function() {
                profilerenderer.script.validate(profilerenderer)
            });
            $(document).on('click', '#scripting .cancel', function() {
                profilerenderer.script.cancel(profilerenderer)
            });
            $(document).on('click', '#scripting .clear', function() {
                profilerenderer.script.clear(profilerenderer)
            });

            $(document).on('click', '.icon.condition-trash', function() {
                let conditionsCount;
                let row = $(this).parents(".conditions-row");
                // If last condition is deleted, add the nocondition template
                conditionsCount = $(this).parents(".order_rule").find(".conditions-row").length; // number of conditions in the current rule
                if (conditionsCount == 1) {
                    profilerenderer.renderNoCondition('ou-nocondition-template', $(this).parents(".order_rule").find(".conditions-title"));
                }

                if (row.prev(".conditions-row").length == 0) { // if there is no previous element, delete the operand or the following condition
                    row.next(".conditions-row").find(".can-toggle").remove();
                }

                // Remove the condition row
                row.prev("hr").remove();
                $(row).remove();

                profilerenderer.saveRules();
            });
            $(document).on('click', '.icon.action-trash', function() {
                let row = $(this).parents(".actions-row");
                $(row).remove();
                profilerenderer.saveRules();
            });
            $(document).on('click', '.icon.rule-trash', function() {
                let row = $(this).parents(".rule-row");
                $(row).remove();
                profilerenderer.saveRules();
            });

            $(document).on('click', '.rule-row .link', function() {
                let row = $(this).parents(".rule-row");
                $(row).toggleClass("disabled");
                profilerenderer.saveRules();
            });

            $(document).on('click', '.cell.body.nocondition', function() {
                $(this).parents(".order_rule").find('.icon.condition-add').click();
                return false;
            });

            $(document).on('click', '.icon.condition-add', function() {
                let row = $(this).parents(".conditions-row");
                if (row.length == 0) {
                    profilerenderer.renderCondition('ou-condition-template', $(this).parents(".order_rule").find(".conditions-title"), 'after', true);
                } else {
                    profilerenderer.renderCondition('ou-condition-template', row, 'after', true);
                }
                profilerenderer.saveRules();
            });

            $(document).on('click', '.icon.action-add', function() {
                profilerenderer.renderAction('ou-action-template', $(this).parents(".actions-row"), 'after', true, {});
                profilerenderer.colorpicker(); // activate colorpicker
                profilerenderer.saveRules();
            });

            $(document).on('click', '.icon.rule-add', function() {
                profilerenderer.renderRule('','ou-rule-template','ou-condition-template', 'ou-action-template', $(this).parents(".rule-row"), 'after', true);
                profilerenderer.colorpicker(); // activate colorpicker
                profilerenderer.saveRules();
            });

            $(document).on('click', '.icon.tagrule', function() {
                let label = $(this).parents(".rule-row").find(".cell.rule-title"); // get label span
                let labelInput = label.find(":input");
                $(this).toggleClass('active');
                label.toggleClass("hidden"); // toggle input visibility
                if (label.hasClass("hidden")) { // reset input if it is hidden
                    labelInput.val('');
                }
            });

            $(document).on('click', '.icon.tag', function() {
                let label = $(this).parents(".actions-row").find(".cell.action-title"); // get label span
                let labelInput = label.find(":input");
                label.toggleClass("hidden"); // toggle input visibility
                if (label.hasClass("hidden")) { // reset input if it is hidden
                    labelInput.val('');
                }
            });
        },
        sortable: function () {
            var profilerenderer;
            profilerenderer = this;
            jQuery('#rules-area').sortable({
                handle: '.grip',
                axis: "y",
                scroll: true,
                only: 'sortable',
                stop: function () {
                    profilerenderer.saveRules();
                }
            })
            jQuery('.actions-list').sortable({
                handle: '.grip-action',
                axis: "y",
                scroll: true,
                only: 'sortable',
                stop: function () {
                    profilerenderer.saveRules();
                }
            })
        },
        getActionElement: function (element, actionOptionIndex) {
            if (element.type == 'select') {
                return this.getActionSelect(element.groups, element.label, actionOptionIndex);
            } else if (element.type == 'input') {
                return this.getActionInput(element.label, actionOptionIndex);
            }
        },
        getActionSelect: function (groups, label, actionOptionIndex) {
            let selectTemplate;
            selectTemplate = mageTemplate("#ou-action-select-template", {
                "actionOptionIndex": actionOptionIndex,
                "groups": groups,
                "label": label
            });
            return selectTemplate;
        },
        getActionInput: function (label, actionOptionIndex) {
            let inputTemplate;
            inputTemplate = mageTemplate("#ou-action-input-template", {
                "actionOptionIndex": actionOptionIndex,
                "label": label
            });
            return inputTemplate;
        },
        /**
         * Update a condition
         * @param {type} parent
         * @param {type} condition
         * @returns {undefined}
         */
        updateCondition: function (parent, condition) {
            let valueTemplate, profilerenderer;
            profilerenderer = this;
            if ($(condition).val() != 'all') {
                $(parent).find(".condition-operand, .value").each(function () {
                    $(this).prop('disabled', false)
                });
            } else {
                $(parent).find(".condition-operand, .value").each(function() {$(this).prop('disabled', true)});
            }
        },
        /**
         * Update a condition operand (enable, disable, ...)
         * @param {type} parent
         * @param {type} condition
         * @returns {undefined}
         */
        updateConditionOperand: function (parent, condition) {
            if ($(condition).val() == 'null' || $(condition).val() == 'notnull') {
                jQuery(parent).find(".value").each(function() {jQuery(this).css('visibility','hidden')});
            } else {
                jQuery(parent).find(".value").each(function() {jQuery(this).css('visibility','visible')});
            }
        },
        /**
         * Update a condition value input
         * @param {type} parent
         * @param {type} previousConditionType
         * @param {type} previousConditionOption
         * @returns {undefined}
         */
        updateConditionValue: function (parent, previousConditionType, previousConditionOption) {
            let valueTemplate, profilerenderer, condition, conditionOperand, valueTemplateId, valueTemplateOptions;
            valueTemplateId = false;
            valueTemplateOptions = '';
            profilerenderer = this;
            condition = parent.find(".condition");
            conditionOperand = parent.find(".condition-operand");

            // manage change on the condition
            if (previousConditionType == 'condition') {
                if (condition.val() == 'order.getState') {
                    valueTemplateOptions = profilerenderer.orderStates;
                    if (conditionOperand.val() == 'in' || conditionOperand.val() == 'nin') {
                        valueTemplateId = '#ou-condition-value-multiselect-template';
                    } else {
                        valueTemplateId = '#ou-condition-value-select-template';
                    }
                } else if (condition.val() == 'order.getStatus') {
                    valueTemplateOptions = profilerenderer.orderStatuses;
                    if (conditionOperand.val() == 'in' || conditionOperand.val() == 'nin') {
                        valueTemplateId = '#ou-condition-value-multiselect-template';
                    } else {
                        valueTemplateId = '#ou-condition-value-select-template';
                    }
                } else if (condition.val() == 'order.getStoreId') {
                    valueTemplateOptions = profilerenderer.stores;
                    if (conditionOperand.val() == 'in' || conditionOperand.val() == 'nin') {
                        valueTemplateId = '#ou-condition-value-multiselect-template';
                    } else {
                        valueTemplateId = '#ou-condition-value-select-template';
                    }
                } else if (previousConditionOption == 'order.getState' || previousConditionOption == 'order.getStatus' || previousConditionOption == 'order.getStoreId') {
                    valueTemplateId = '#ou-condition-value-input-template';
                }
            }

            // manage change on the condition operand
            if (previousConditionType == 'condition-operand') {
                if (condition.val() == 'order.getState') {
                    valueTemplateOptions = profilerenderer.orderStates;
                    if (conditionOperand.val() == 'in' || conditionOperand.val() == 'nin') {
                        if (previousConditionOption != 'in' && previousConditionOption != 'nin') {
                            valueTemplateId = '#ou-condition-value-multiselect-template';
                        }
                    } else {
                        if (previousConditionOption == 'in' || previousConditionOption == 'nin') {
                            valueTemplateId = '#ou-condition-value-select-template';
                        }
                    }
                } else if (condition.val() == 'order.getStatus') {
                    valueTemplateOptions = profilerenderer.orderStatuses;
                    if (conditionOperand.val() == 'in' || conditionOperand.val() == 'nin') {
                        if (previousConditionOption != 'in' && previousConditionOption != 'nin') {
                            valueTemplateId = '#ou-condition-value-multiselect-template';
                        }
                    } else {
                        if (previousConditionOption == 'in' || previousConditionOption == 'nin') {
                            valueTemplateId = '#ou-condition-value-select-template';
                        }
                    }
                } else if (condition.val() == 'order.getStoreId') {
                    valueTemplateOptions = profilerenderer.stores;
                    if (conditionOperand.val() == 'in' || conditionOperand.val() == 'nin') {
                        if (previousConditionOption != 'in' && previousConditionOption != 'nin') {
                            valueTemplateId = '#ou-condition-value-multiselect-template';
                        }
                    } else {
                        if (previousConditionOption == 'in' || previousConditionOption == 'nin') {
                            valueTemplateId = '#ou-condition-value-select-template';
                        }
                    }
                } else if (previousConditionOption == 'order.getState' || previousConditionOption == 'order.getStatus' || previousConditionOption == 'order.getStoreId') {
                    valueTemplateId = '#ou-condition-value-input-template';
                }
            }

            if (valueTemplateId) {
                valueTemplate = mageTemplate(valueTemplateId, {
                    "options": valueTemplateOptions
                });
                $(parent).find('.value').parent().html(valueTemplate);
                $(parent).find('.value').change(); // save the default value
            }
        },
        saveRules: function () {
            let selectedRules;
            selectedRules = {};

            // gather rules information
            $("#rules-area .rule-row").each(function (index) {
                var ruleIndex;
                ruleIndex = index;
                selectedRules[ruleIndex] = {};
                selectedRules[ruleIndex]['disabled'] = $(this).hasClass("disabled");
                selectedRules[ruleIndex]['name'] = $(this).find(".rtitle").val();
                selectedRules[ruleIndex]['conditions'] = {};
                selectedRules[ruleIndex]['actions'] = {};
                $(this).find(".conditions-row").each(function (conditionIndex) {
                    selectedRules[ruleIndex]['conditions'][conditionIndex] = {};
                    let operandValue = '';
                    if ($(this).find(":checkbox.operand").length) {
                        if ($(this).find(":checkbox.operand").is(':checked')) {
                            operandValue = 'and';
                        } else {
                            operandValue = 'or';
                        }
                    }
                    selectedRules[ruleIndex]['conditions'][conditionIndex]['operand'] = operandValue;
                    selectedRules[ruleIndex]['conditions'][conditionIndex]['condition'] = $(this).find(".condition").val();
                    selectedRules[ruleIndex]['conditions'][conditionIndex]['condition-operand'] = $(this).find(".condition-operand").val();
                    selectedRules[ruleIndex]['conditions'][conditionIndex]['value'] = $(this).find(".value").val();
                });
                $(this).find(".actions-row").each(function (actionIndex) {
                    selectedRules[ruleIndex]['actions'][actionIndex] = {};
                    $(this).find("input, select, textarea").each(function (element) {
                        selectedRules[ruleIndex]['actions'][actionIndex][$(this).attr("class")] = $(this).val();
                    });
                });
            });
            $("#rules").val(JSON.stringify(selectedRules));
        },
        getUniqueId: function () {
            let uniqueId;
            uniqueId = '';
            while (uniqueId == '' || document.getElementById(uniqueId)) {
                uniqueId = Math.floor(Math.random() * 100000);
            }
            return uniqueId;
        },
        script: {
            row: null,
            editor: null,
            open: function (row, profilerenderer) {
                    this.row = row;
                    var value = row.find("[class*=-script]").val().replace(/__LINE_BREAK__/g, "\n");
                    if (value.trim() == '') {
                        value = "<?php\n /* Your custom script */\n return $self;\n";
                    }
                    $("#scripting #codemirror").val(value);

                    this.editor = profilerenderer.createCodeMirror($("#scripting #codemirror").get(0), "application/x-httpd-php-open");

                    profilerenderer.script.row = row;
                    $("#overlay").css({display: 'block'})
                    $("#scripting").draggable({handle: '.handler'});
                },
            clear: function (profilerenderer) {
                this.editor.setValue('');
                profilerenderer.script.validate(profilerenderer);
            },
            validate: function (profilerenderer) {
                if (this.editor) {
                    profilerenderer.script.row.find("[class*=-script]").val(this.editor.getValue().replace(/(?:\r\n|\r|\n)/g, "__LINE_BREAK__"));
                    if (this.editor.getValue() != "") {
                        profilerenderer.script.row.find(".code").addClass("active");
                        profilerenderer.script.row.find(".default").addClass("invisible").val();
                    } else {
                        profilerenderer.script.row.find(".code").removeClass("active");
                        if (profilerenderer.script.row.hasClass("sortable")) {
                            if (profilerenderer.script.row.find(".source").val() == '') {
                                profilerenderer.script.row.find(".default").removeClass("invisible");
                            } else {
                                profilerenderer.script.row.find(".default").addClass("invisible");
                            }
                        }
                    }
                }
                profilerenderer.saveRules();
                profilerenderer.script.close(profilerenderer)
            },
            cancel: function (profilerenderer) {
                profilerenderer.script.close(profilerenderer)
            },
            close: function (profilerenderer) {
                if (this.editor) {
                    this.editor.setValue('');
                    this.editor.toTextArea();
                }
                profilerenderer.script.row = null;
                $("#overlay").css({display: 'none'})
            }
        }
    }
});