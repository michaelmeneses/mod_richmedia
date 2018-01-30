/**
 * Js of the module
 * Author:
 * 	Adrien Jamot  (adrien_jamot [at] symetrix [dt] fr)
 * 
 * @package   mod_richmedia
 * @copyright 2011 Symetrix
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v2 or later
 */

M.mod_richmedia = {
    init: function(Y, richmediaid) {
    },
    initPlayerHTML5: function(Y, richmediainfos, audioMode) {
        Player.init(richmediainfos, audioMode);
    },
    initReport: function(Y, id, richmediaId) {
        this.id = id;
        this.richmediaId = richmediaId;
        var that = this;
        $('#checkall').on('click', function() {
            that.checkAll(true);
        });
        $('#uncheckall').on('click', function() {
            that.checkAll(false);
        });
        $('#deleterows').on('click', function() {
            that.deleteRows();
        });
    },
    deleteAll: function(joined) {
        $.post('report.php', {
            action: 'delete',
            id: this.id,
            richmediaid: this.richmediaId,
            joined: joined
        }).done(function(data) {
            window.location.reload(true);
        });
    },
    checkAll: function(bool) {
        var inputs = document.getElementsByTagName('input');
        for (var k = 0; k < inputs.length; k++) {
            var input = inputs[k];
            if (input.type === "checkbox") {
                input.checked = bool;
            }
        }
    },
    deleteRows: function() {
        var inputs = document.getElementsByTagName('input');
        var checked = new Array();
        for (var k = 0; k < inputs.length; k++) {
            var input = inputs[k];
            if (input.type == "checkbox" && input.checked) {
                checked.push(input.id);
            }
        }
        if (checked.length == 0) {
            alert(M.util.get_string('noselectedline', 'mod_richmedia'));
        }
        else {
            var joined = checked.join(',');
            this.deleteAll(joined);
        }
    },
    editRow: function(id) {
        var that = this;
        var the = this.storetheme.getById(id);
        var panelEdit = new Ext.form.FormPanel({
            fileUpload: true,
            width: 450,
            height: 200,
            bodyStyle: 'padding: 10px 10px 10px 10px;',
            labelWidth: 50,
            defaults: {
                anchor: '95%',
                allowBlank: false,
                msgTarget: 'side'
            },
            items: [
                {
                    xtype: 'hidden',
                    name: 'anciennom',
                    value: the.data.nom
                }, {
                    xtype: 'textfield',
                    name: 'nom',
                    fieldLabel: M.util.get_string('name', 'mod_richmedia'),
                    vtype: 'alphanum',
                    value: the.data.nom
                }, {
                    xtype: 'panel',
                    border: false,
                    html: M.util.get_string('logo', 'mod_richmedia') + ':&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="logoupload" type="file" size="50" maxlength="100000">'
                }, {
                    xtype: 'panel',
                    border: false,
                    html: M.util.get_string('fond', 'mod_richmedia') + ':&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="backgroundupload" type="file" size="50" maxlength="100000">'
                }
            ]
        });
        var winEdit = new Ext.Window({
            title: M.util.get_string('themeedition', 'mod_richmedia'),
            closeAction: 'close',
            layout: 'fit',
            resizable: false,
            height: 180,
            width: 500,
            items: [panelEdit],
            buttonAlign: 'center',
            buttons: [{
                    text: 'OK',
                    handler: function() {
                        panelEdit.getForm().submit({
                            url: "save_theme.php?edit=1",
                            waitTitle: M.util.get_string('wait', 'mod_richmedia'),
                            timeout: 3500000,
                            waitMsg: M.util.get_string('currentsave', 'mod_richmedia'),
                            success: function(obj, action) {
                                Ext.Msg.show({
                                    title: M.util.get_string('success', 'mod_richmedia'),
                                    msg: M.util.get_string('importdone', 'mod_richmedia'),
                                    buttons: Ext.Msg.OK
                                });
                                delete that.storetheme.lastParams;
                                that.storetheme.reload();
                                winEdit.close();
                            },
                            failure: function(form, action) {
                                Ext.Msg.show({
                                    title: M.util.get_string('error', 'mod_richmedia'),
                                    msg: action.result.msg.reason,
                                    buttons: Ext.Msg.OK
                                });
                            }
                        });
                    }
                }, {
                    text: M.util.get_string('cancel', 'mod_richmedia'),
                    handler: function() {
                        winEdit.close();
                    }
                }]
        });
        winEdit.show();
    },
    deleteRow: function(id) {
        var that = this;
        var the = this.storetheme.getById(id);
        Ext.Msg.show({
            title: M.util.get_string('warning', 'mod_richmedia'),
            msg: M.util.get_string('removetheme', 'mod_richmedia') + ' ' + the.data.nom + ' ?',
            buttons: Ext.Msg.YESNO,
            fn: function(btn) {
                if (btn == 'yes') {
                    Ext.Ajax.request({
                        url: 'save_theme.php?delete=1'
                        , method: 'POST'
                        , params: {
                            nom: the.data.nom
                        }
                        , success: function(result, request) {
                            if (result.responseText == 1) {
                                Ext.Msg.show({
                                    title: M.util.get_string('information', 'mod_richmedia'),
                                    msg: M.util.get_string('deletedtheme', 'mod_richmedia'),
                                    buttons: Ext.Msg.OK
                                });
                                delete that.storetheme.lastParams;
                                that.storetheme.reload();
                            }
                        }
                    });
                }
            }
        });
    },
    setModForm: function() {
        $('#id_fontcolor').iris();
    }
};