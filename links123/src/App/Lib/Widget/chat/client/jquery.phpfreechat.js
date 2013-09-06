/*jslint node: true, maxlen: 100, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's authentication related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  /**
   * Login the user
   * Shows a popup if no credentials are provided
   */
  pfc.login = function (credentials, callback) {
    var h = credentials ? { 'Pfc-Authorization': 'Basic '
                            + pfc.base64.encode(credentials.login + ':' + credentials.password) }
                        : {};
    var d = credentials ? {'email': credentials.email} : null;
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/auth',
      headers: h,
      data: d
    }).done(function (userdata) {
      $(pfc.element).trigger('pfc-login', [ pfc, userdata ]);
      if (callback) { callback(null, userdata) }
    }).error(function (err) {
      try {
        err = JSON.parse(err.responseText);
      } catch (e) {
      }
      if (err &&
          err.error &&
          err.errorCode != 40301) { // != Need authentication
        pfc.showAuthForm(err.error);
      } else {
        pfc.showAuthForm();
      }
      if (callback) { callback(err) }
    });
  };

  /**
   * Logout the user from the chat
   */
  pfc.logout = function (callback) {
    $.ajax({
      type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
      url:  pfc.options.serverUrl + '/auth',
      data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : null
    }).done(function (userdata) {
      $(pfc.element).trigger('pfc-logout', [ pfc, userdata ]);
      if (callback) { callback(null, userdata) }
    }).error(function (err) {
      if (callback) { callback(err) }
    });
  };

  /**
   * Open the authentication dialog box (login, email, password)
   * msg: message to display
   */
  pfc.showAuthForm = function (msg) {
    pfc.modalbox.open(
        '<form>'
      + '  <div class="popup-login">'
      + '  <input type="text" name="login" placeholder="输入一个名字"/><br/>'
     // + '  <input type="text" name="password" placeholder="密码"/><br/>'
      + '  <input type="text" name="email" placeholder="Email"/><br/>'
      + '    <input type="submit" name="submit" value="进入" />'
      + (msg ? '<p>' + msg + '</p>' : '')
      + '  </div>'
      + '</form>'
    ).submit(function () {
      var login    = $(this).find('[name=login]').attr('value');
      var password = $(this).find('[name=password]').attr('value');
      var email    = $(this).find('[name=email]').attr('value');
      if (!login) { return false; }
      
      pfc.login({'login': login, 'password': password, 'email': email});
      pfc.modalbox.close(true);
      
      return false;
    }).find('[name=login]').focus();
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's channel related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

//  pfc.channels = {};
  
  /**
   * Returns the channel name of the given channel id
   */
  pfc.getNameFromCid = function (cid) {
    return pfc.channels[cid].name;
  };
  
  /**
   * Returns the channel id of the given channel name
   */
  pfc.getCidFromName = function (channel) {
    var result = null;
    $.each(pfc.channels, function (cid, chan) {
      if (channel === chan.name) {
        result = cid;
      }
    });
    return result;
  };
 
  /**
   * Add a user to the channel structure
   */
  pfc.addUidToCid = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].users);
    if (idx === -1) {
      pfc.channels[cid].users.push(uid);
      return true;
    } else {
      return false;
    }
  };
  
  /**
   * Remove a user from the channel structure
   */
  pfc.removeUidFromCid = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].users);
    if (idx === -1) {
      return false;
    } else {
      pfc.channels[cid].users.splice(idx, 1);
      pfc.channels[cid].op.splice(idx, 1);
      return true;
    }
  };
  
  /**
   * Add a user to the channel's operators
   */
  pfc.addUidToCidOp = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].op);
    if (idx === -1) {
      pfc.addUidToCid(uid, cid);
      pfc.channels[cid].op.push(uid);
      return true;
    } else {
      return false;
    }
  };

  /**
   * Remove a user from the channel's operators
   */
  pfc.removeUidFromCidOp = function (uid, cid) {
    var idx = $.inArray(uid , pfc.channels[cid].op);
    if (idx === -1) {
      return false;
    } else {
      pfc.channels[cid].op.splice(idx, 1);
      return true;
    }
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's ban commands
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * ban command
   */
  pfc.commands.ban = {
    usage:      '/ban "<username>" ["reason"]',
    longusage:  '/ban ["#<channel>"] "<username>" ["reason"]',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"([^#][^"]*?)" +"([^"]+?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/,      
      /^"#([^"]+?)" +"([^"]+?)" +"([^"]+?)"$/
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'username', 2: 'reason' },
      { 1: 'channel', 2: 'username' },
      { 1: 'channel', 2: 'username', 3: 'reason' }
    ],
    
    send: function (cmd_arg) {
      var name64 = pfc.base64.encode(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/' + name64,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT', reason: cmd_arg.reason } : { reason: cmd_arg.reason }
      }).done(function () {
        
        pfc.commands.ban.receive({
          type: 'ban',
          sender: pfc.uid,
          body: { opname: pfc.users[pfc.uid].name, name: cmd_arg.username, reason: cmd_arg.reason, kickban: false },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];

      if (pfc.users[pfc.uid].name == msg.body.name) {
        // someone banned me
        
        // if i was also kicked from the channel
        if (msg.body.kickban) {
          pfc.clearUserList();
          // todo: close the tab & update the current channel         
        }

        // post a message
        msg.body = 'You were ' + (msg.body.kickban ? 'kick' : '') + 'banned by ' + msg.body.opname +
                   ' from #'  + pfc.getNameFromCid(cid) +
                   ' for ' + (msg.body.reason ? 'the reason "' + msg.body.reason + '"' : 'no reason');
        pfc.appendMessage(msg);

      } else {
        // someone banned someone (not me)

        // if the user was also kicked from the channel
        if (msg.body.kickban) {
          // update the channel operator list structure
          pfc.removeUidFromCid(pfc.getUidFromName(msg.body.name), cid);
          // update the users list interface
          pfc.removeUser(pfc.getUidFromName(msg.body.name));
        }
        
        // post a message
        msg.body = msg.body.name + ' was ' + (msg.body.kickban ? 'kick' : '') + 'banned by ' + msg.body.opname +
                   ' from this channel' +
                   ' for ' + (msg.body.reason ? 'the reason "' + msg.body.reason + '"' : 'no reason');
        pfc.appendMessage(msg);
        
      }
    }
  };

  /**
   * kickban command
   */
  pfc.commands.kickban = {
    usage:      '/kickban "<username>" ["reason"]',
    longusage:  '/kickban ["#<channel>"] "<username>" ["reason"]',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"([^#][^"]*?)" +"([^"]+?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/,      
      /^"#([^"]+?)" +"([^"]+?)" +"([^"]+?)"$/
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'username', 2: 'reason' },
      { 1: 'channel', 2: 'username' },
      { 1: 'channel', 2: 'username', 3: 'reason' }
    ],
    
    send: function (cmd_arg) {
      var name64 = pfc.base64.encode(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/' + name64,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT', reason: cmd_arg.reason, kickban: true } : { reason: cmd_arg.reason }
      }).done(function () {
        
        pfc.commands.ban.receive({
          type: 'ban',
          sender: pfc.uid,
          body: { opname: pfc.users[pfc.uid].name, name: cmd_arg.username, reason: cmd_arg.reason, kickban: true },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: pfc.commands.ban.receive
  };

  /**
   * unban command
   */
  pfc.commands.unban = {
    usage:      '/unban "<username>"',
    longusage:  '/unban ["#<channel>"] "<username>"',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'channel', 2: 'username' }
    ],
    
    send: function (cmd_arg) {
      var name64 = pfc.base64.encode(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/' + name64,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : { }
      }).done(function () {
        
        pfc.commands.unban.receive({
          type: 'unban',
          sender: pfc.uid,
          body: { opname: pfc.users[pfc.uid].name, name: cmd_arg.username },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];
      
      // todo: post the message only on the concerned channel
      // post a message
      msg.body = msg.body.name + ' was unbanned by ' + msg.body.opname +
                  ' from #'  + pfc.getNameFromCid(cid);
      pfc.appendMessage(msg);
    }
  };

  /**
   * banlist command
   */
  pfc.commands.banlist = {
    usage:      '/banlist',
    longusage:  '/banlist ["#<channel>"]',
    regexp:     [
      /^$/,
      /^"#([^"]+?)"$/
    ],
    regexp_ids: [
      { },
      { 1: 'channel' }
    ],
    
    send: function (cmd_arg) {
      $.ajax({
        type: 'GET',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/ban/'
      }).done(function (banlist) {
        
        pfc.commands.banlist.receive({
          type: 'banlist',
          sender: pfc.uid,
          body: banlist,
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];

      var banlist_txt = [];
      $.each(msg.body, function (key, value) {
        value.timestamp = new Date(value.timestamp * 1000);
        banlist_txt.push(
          key + ' (banned by ' + value.opname +
          ' for ' + (value.reason ? 'the reason "' + value.reason + '"' : 'no reason') +
          ' on ' + value.timestamp +
          ')');
      });
      if (banlist_txt.length > 0) {
        msg.body = 'Banished list on #' + pfc.getNameFromCid(cid) + '\n  - ' + banlist_txt.join('\n  - ');
      } else {
        msg.body = 'Empty banished list on  #' + pfc.getNameFromCid(cid);
      }
      pfc.appendMessage(msg);      
    }
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's join/leave commands
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * join command
   */
  pfc.commands.join = {
    help:       '',
    usage:      '/join "#<channel>"',
    longusage:  '/join "#<channel>"',
    regexp:     [ /^"#([^"]+?)"$/ ],
    regexp_ids: [ { 1: 'channel' } ],
    
    send: function (cmd_arg) {
      
      // todo : POST to /channels/ route to require a cid for the channel name (cmd_arg.channel)
      cmd_arg.cid = "xxx";
      
      //console.log(cmd_arg);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/users/' + pfc.uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : null
      }).done(function (cinfo) {
        
        pfc.channels[cmd_arg.cid] = {
          name: cmd_arg.cid,
          users: [],
          op: []
        };
        
        // store channel operators
        pfc.channels[cmd_arg.cid].op = cinfo.op;
        
        // store userdata in the cache
        // refresh the interface
        pfc.clearUserList();
        $.each(cinfo.users, function (uid, udata) {
          pfc.addUidToCid(uid, cmd_arg.cid);
          
          pfc.users[uid] = udata;
          pfc.appendUser(udata);
        });

        // display a join message for himself
        pfc.appendMessage({
          type: 'join',
          sender: pfc.uid,
          body: 'you joined the channel'
        });

      }).error(function (err) {
        pfc.displayError(err);
      });

    },
    
    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];

      // store new user in the channels structure
      pfc.addUidToCid(msg.sender, cid);

      // update the channel operator list structure
      if (msg.body.op) {
        pfc.addUidToCidOp(msg.sender, cid);
      }
      
      // store new joined user data
      pfc.users[msg.sender] = msg.body.userdata;
      
      // append the user to the list
      pfc.appendUser(pfc.users[msg.sender]); 
      
      // display the join message
      pfc.appendMessage(msg);
    }
  };
  
  /**
   * leave command
   */
  pfc.commands.leave = {
    help:       '',
    usage:      '/leave ["#<channel>"]',
    longusage:  '/leave ["#<channel>"] ["reason"]',
    regexp:     [
      /^"#([^"]+?)" "([^"]+?)"$/,
      /^"#([^"]+?)"$/,
      /^"([^"]+?)"$/,
      /^$/
    ],
    regexp_ids: [
      { 1: 'channel', 2: 'reason' },
      { 1: 'channel' },
      { 1: 'reason' },
      { }
    ],
    
    send: function (cmd_arg) {
      //cid, command, channel, reason
      
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/users/' + pfc.uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : null
      }).done(function () {
        pfc.clearUserList();
        
        // display a leave message for himself
        pfc.appendMessage({
          type: 'leave',
          sender: pfc.uid
        });
        
        // todo: close the tab
        
      }).error(function (err) {
        pfc.displayError(err);
      });
      
    },
    
    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];

      pfc.removeUidFromCid(msg.sender, cid);
      pfc.removeUser(msg.sender);
      pfc.appendMessage(msg);
    }
  };

    
  /**
   * op command
   */
  pfc.commands.op = {
    help:       'gives operator rights to a user on a channel',
    usage:      '/op "<username>"',
    longusage:  '/op ["#<channel>"] "<username>"',
    params:     [ 'channel', 'username' ],
    regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
    regexp_ids: [ { 2: 'channel', 3: 'username' } ],
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/op/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : null
      }).done(function (op_info) {
        //console.log(op_info);
        pfc.commands.op.receive({
          type: 'op',
          sender: pfc.uid,
          body: uid,
          recipient: 'channel|' + cmd_arg.cid
        });
      }).error(function (err) {
        console.log(err);
      });
    },
    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];
      var op     = pfc.users[msg.sender];
      var op_dst = pfc.users[msg.body];

      // update the channel operator list structure
      pfc.addUidToCidOp(op_dst.id, cid);

      // append message to the list
      msg.body = op.name + ' gave operator rights to ' + op_dst.name;
      pfc.appendMessage(msg);
      
      // update the users list interface
      pfc.removeUser(op_dst.id);
      pfc.appendUser(op_dst.id);
    }
  };
  

  /**
   * deop command
   */
  pfc.commands.deop = {
    help:       'removes operator rights to a user on a channel',
    usage:      '/deop "<username>"',
    longusage:  '/deop ["#<channel>"] "<username>"',
    params:     [ 'channel', 'username' ],
    regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
    regexp_ids: [ { 2: 'channel', 3: 'username' } ],
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/op/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : null
      }).done(function (op_info) {
        //console.log(op_info);
        pfc.commands.deop.receive({
          type: 'deop',
          sender: pfc.uid,
          body: uid,
          recipient: 'channel|' + cmd_arg.cid
        });
      }).error(function (err) {
        console.log(err);
      });
    },
    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];
      var deop     = pfc.users[msg.sender];
      var deop_dst = pfc.users[msg.body];

      // update the channel operator list structure
      pfc.removeUidFromCidOp(deop_dst.id, cid);

      // append message to the list
      msg.body = deop.name + ' removed operator rights to ' + deop_dst.name;
      pfc.appendMessage(msg);

      // update the users list
      pfc.removeUser(deop_dst.id);
      pfc.appendUser(deop_dst.id);
    }
  };
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's kick command
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * kick command
   */
  pfc.commands.kick = {
    usage:      '/kick "<username>" ["reason"]',
    longusage:  '/kick ["#<channel>"] "<username>" ["reason"]',
    regexp:     [
      /^"([^#][^"]*?)"$/,
      /^"([^#][^"]*?)" +"([^"]+?)"$/,
      /^"#([^"]+?)" +"([^"]+?)"$/,      
      /^"#([^"]+?)" +"([^"]+?)" +"([^"]+?)"$/,      
    ],
    regexp_ids: [
      { 1: 'username' },
      { 1: 'username', 2: 'reason' },
      { 1: 'channel', 2: 'username' },
      { 1: 'channel', 2: 'username', 3: 'reason' }
    ],
    
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/users/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE', reason: cmd_arg.reason } : { reason: cmd_arg.reason }
      }).done(function () {
        
        pfc.commands.kick.receive({
          type: 'kick',
          sender: pfc.uid,
          body: { target: uid, reason: cmd_arg.reason },
          recipient: 'channel|' + cmd_arg.cid
        });
        
      }).error(function (err) {
        console.log(err);
      });
    },

    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];
      var kicker = pfc.users[msg.sender];
      var kicked = pfc.users[msg.body.target];

      if (pfc.uid == kicked.id) {
        pfc.clearUserList();

        // append message to the list
        msg.body = kicker.name + ' kicked you from ' + pfc.getNameFromCid(cid) + (msg.body.reason ? (' [ reason: ' + msg.body.reason + ']') : '');
        pfc.appendMessage(msg);
        
        // todo: close the tab
      } else {
        // update the channel operator list structure
        pfc.removeUidFromCid(kicked.id, cid);

        // append message to the list
        msg.body = kicker.name + ' kicked ' + kicked.name + (msg.body.reason ? (' [ reason: ' + msg.body.reason + ']') : '');
        pfc.appendMessage(msg);
        
        // update the users list interface
        pfc.removeUser(kicked.id);
      }
    }
  };
    
  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's op/deop commands
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);

  /**
   * op command
   */
  pfc.commands.op = {
    help:       'gives operator rights to a user on a channel',
    usage:      '/op "<username>"',
    longusage:  '/op ["#<channel>"] "<username>"',
    params:     [ 'channel', 'username' ],
    regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
    regexp_ids: [ { 2: 'channel', 3: 'username' } ],
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/op/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : null
      }).done(function (op_info) {
        //console.log(op_info);
        pfc.commands.op.receive({
          type: 'op',
          sender: pfc.uid,
          body: uid,
          recipient: 'channel|' + cmd_arg.cid
        });
      }).error(function (err) {
        console.log(err);
      });
    },
    receive: function (msg) {
      var cid    = msg.recipient.split('|')[1];
      var op     = pfc.users[msg.sender];
      var op_dst = pfc.users[msg.body];

      // update the channel operator list structure
      pfc.addUidToCidOp(op_dst.id, cid);

      // append message to the list
      msg.body = op.name + ' gave operator rights to ' + op_dst.name;
      pfc.appendMessage(msg);
      
      // update the users list interface
      pfc.removeUser(op_dst.id);
      pfc.appendUser(op_dst.id);
    }
  };
  

  /**
   * deop command
   */
  pfc.commands.deop = {
    help:       'removes operator rights to a user on a channel',
    usage:      '/deop "<username>"',
    longusage:  '/deop ["#<channel>"] "<username>"',
    params:     [ 'channel', 'username' ],
    regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
    regexp_ids: [ { 2: 'channel', 3: 'username' } ],
    send: function (cmd_arg) {
      var uid = pfc.getUidFromName(cmd_arg.username);
      $.ajax({
        type: pfc.options.use_post_wrapper ? 'POST' : 'DELETE',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/op/' + uid,
        data: pfc.options.use_post_wrapper ? { _METHOD: 'DELETE' } : null
      }).done(function (op_info) {
        //console.log(op_info);
        pfc.commands.deop.receive({
          type: 'deop',
          sender: pfc.uid,
          body: uid,
          recipient: 'channel|' + cmd_arg.cid
        });
      }).error(function (err) {
        console.log(err);
      });
    },
    receive: function (msg) {
      var cid = msg.recipient.split('|')[1];
      var deop     = pfc.users[msg.sender];
      var deop_dst = pfc.users[msg.body];

      // update the channel operator list structure
      pfc.removeUidFromCidOp(deop_dst.id, cid);

      // append message to the list
      msg.body = deop.name + ' removed operator rights to ' + deop_dst.name;
      pfc.appendMessage(msg);

      // update the users list
      pfc.removeUser(deop_dst.id);
      pfc.appendUser(deop_dst.id);
    }
  };
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's commands related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  pfc.commands = $.extend({}, pfc.commands);
  
  /**
   * msg command
   */
  pfc.commands.msg = {
    usage:      '/msg "<message>"',
    longusage:  '/msg ["#<channel>"] "<message>"',
    regexp:     [ /^("#(.+?)" |)"(.+?)"$/ ],
    regexp_ids: [ { 2: 'channel', 3: 'message' } ],
    
    send: function (cmd_arg) {
      // post the command to the server
      $.ajax({
        type: 'POST',
        url:  pfc.options.serverUrl + '/channels/' + cmd_arg.cid + '/msg/',
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify(cmd_arg.message)
      }).done(function (msg) {
        pfc.commands.msg.receive(msg);
      }).error(function (err) {
        console.log(err);
      });
    },
    
    receive: function (msg) {
      // display the message on the chat interface
      pfc.appendMessage(msg);
    }
  };

  /**
   * Parse the sent message
   * Try to extract explicit commands from it
   * Returns: [ <cid>, <cmd>, <cmd-param1>, <cmd-param2>, ... ] 
   */
  pfc.parseCommand = function (raw) {
    
    var cmd     = '';
    var cmd_arg = null;
    
    // test each commands on the raw message
    $.each(pfc.commands, function (c) {
      // first of all, try to reconize a /<command> pattern
      if (new RegExp('^\/' + c + '( |$)').test(raw)) {
        cmd = c;
        // parse the rest of the command line (the end)
        var raw_end = new RegExp('^\/' + c + ' *(.*)$').exec(raw)[1];
        $.each(pfc.commands[c].regexp, function (i, regexp) {
          var cmd_arg_tmp = regexp.exec(raw_end);
          if (cmd_arg === null && cmd_arg_tmp && cmd_arg_tmp.length > 0) {
            // collect interesting values from the regexp result
            cmd_arg = {};
            $.each(pfc.commands[c].regexp_ids[i], function (id, key) {
              cmd_arg[key] = cmd_arg_tmp[id]
            });
//             console.log("------------");
//             console.log(i);
//             console.log(regexp);
//             console.log(cmd_arg_tmp);
//             console.log(cmd_arg);
//             console.log("------------");
          }
        });
      }
    });
    
    // if no /<command> pattern found, considere it's a /msg command
    if (cmd === '') {
      cmd     = 'msg';
      cmd_arg = {
        cid:     pfc.cid,
        message: raw
      };
    }

    // return an error if the command parameters do not match
    if (cmd_arg === null) {
      throw [ cmd, pfc.commands[cmd].usage ];
    }    
    
    // optionaly fill channel value if user didn't indicate it
    if (!cmd_arg.cid) {
      if (!cmd_arg.channel) {
        // no channel has been indicated, we have to used the current one
        cmd_arg.cid = pfc.cid;
      } else {
        // one channel has been indicated, we have to translate the channel name to the corresponding cid
        cmd_arg.cid = pfc.getCidFromName(cmd_arg.channel);
      }
    }

    return [ cmd, cmd_arg ];
  };
  
  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's core functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {


  /**
   * Read current user pending messages
   */
  pfc.readPendingMessages = function (loop) {

    // initialize the network error counter
    if (pfc.readPendingMessages.nb_network_error === undefined) {
      pfc.readPendingMessages.nb_network_error = 0;
    }
    
    // send periodicaly AJAX request to check pending messages
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/users/' + pfc.uid + '/pending/'
    }).done(function (msgs) {
      // reset the error counter because a request has been well received
      pfc.readPendingMessages.nb_network_error = 0;

      $.each(msgs, function (i, m) {
        // specific actions for special messages
        if (pfc.commands[m.type] !== undefined) {
          pfc.commands[m.type].receive(m);
        } else {
          pfc.showErrorsPopup([ 'Unknown command ' + m.type ]);          
        }
      });
      if (loop) {
        setTimeout(function () { pfc.readPendingMessages(true) }, pfc.options.refresh_delay);
      }
    }).error(function (err) {
      // check how many network errors has been received and
      // block the automatic refresh if number of allowed errors is exceed
      if (pfc.readPendingMessages.nb_network_error++ > pfc.options.tolerated_network_errors) {
        pfc.showErrorsPopup([ 'Network error. Please reload the chat to continue.' ]);
      } else if (loop) {
        setTimeout(function () { pfc.readPendingMessages(true) }, pfc.options.refresh_delay);
      }
    });

  };

  /**
   * Join a channel
   */
  pfc.join = function (cid) {
    pfc.postCommand('/join "#xxx"');
  };
  
  /**
   * Wrapper for the leave a channel
   */
  pfc.leave = function (cid) {
    pfc.postCommand('/leave "#xxx"');
  };

  /**
   * Post a command to the server
   */
  pfc.postCommand = function (raw_cmd) {

    // do not execute empty command
    if (raw_cmd === '') {
      return false;
    }
    
    try {
      // parse command
      var cmd = pfc.parseCommand(raw_cmd);
      // send the command to the server
      pfc.commands[cmd[0]].send(cmd[1]);
    } catch (err) {
      // caught a command parsing error
      pfc.appendMessage({
        from: 'system-error',
        body: 'Invalid command syntax. Usage:\n' + err[1]
      });
    }

  };
  
  /**
   * Notify phpfreechat server that a windows close event occured
   * Thanks to this notification, server can tell other users that this user just leave the channels
   */
  pfc.notifyThatWindowIsClosed = function () {
    $.ajax({
      type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
      async: false, // important or this request will be lost when windows is closed
      url:  pfc.options.serverUrl + '/users/' + pfc.uid + '/closed',
      data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : '1'
    }).done(function () {
      //      console.log('notifyThatWindowIsClosed done');
    }).error(function (err) {
      console.log(err);
    });
  };

  /**
   * Appends a username in the user list
   * returns the id of the user's dom element
   */
  pfc.appendUser = function (user) {

    // be tolerent "user" parameter could be a uid
    if (pfc.users[user]) {
      user = pfc.users[user];
    }
    
    // user.role = admin or user
    // user.name = nickname
    // user.email = user email used to calculate gravatar
    // user.active = true if active
    
    // default values
    user.id     = (user.id !== undefined) ? user.id : 0;
    user.op     = ($.inArray(user.id, pfc.channels[pfc.cid].op) >= 0);
    user.role   = user.op ? 'admin' : 'user';
    user.name   = (user.name !== undefined) ? user.name : 'Guest ' + Math.round(Math.random() * 100);
    user.email  = (user.email !== undefined) ? user.email : '';
    user.active = (user.active !== undefined) ? user.active : true;
    
    // user list DOM element
    var users_dom = $(pfc.element).find(user.role == 'admin' ? 'div.pfc-role-admin' :
                                                               'div.pfc-role-user');

    // create a blank DOM element for the user
    var html = $('              <li class="user">'
               + '                <div class="status"></div>'
               + '                <div class="name"></div>'
               + '                <div class="avatar"></div>'
               + '              </li>');

    // fill the DOM element
    if (user.name) {
      html.find('div.name').text(user.name);
    }
    if (users_dom.find('li').length === 0) {
      html.addClass('first');
    }
    html.find('div.status').addClass(user.active ? 'st-active' : 'st-inactive');

    // operators have a specific icon in the user list
    if (user.op) {
      html.find('div.status').addClass('st-op');
    }
    
    //html.find('div.avatar').append('<img src="http://www.gravatar.com/avatar/' + pfc.md5(user.email) + '?d=wavatar&amp;s=20" alt="" />');

    // get all userids from the list (could be cached)
    var userids = [];
    $(pfc.element).find('div.pfc-users li.user').each(function (i, dom_user) {
      userids.push(parseInt($(dom_user).attr('id').split('_')[1], 10));
    });
    // if no user id is indicated, generate a new one
    if (user.id === 0) {
      do {
        user.id = Math.round(Math.random() * 10000);
      } while ($.inArray(user.id, userids) !== -1);
    }
    // add the id in the user's dom element
    if (user.id !== 0 && $.inArray(user.id, userids) === -1) {
      html.attr('id', 'user_' + user.id);
    } else {
      return 0;
    }

    // append the user dom element to the interface
    users_dom.find('ul').append(html);
    pfc.updateRolesTitles();

    return user.id;
  };
  
  /**
   * Remove a user from the user list
   * returns true if user has been found, else returns false
   */
  pfc.removeUser = function (uid) {
    var removed = ($(pfc.element).find('#user_' + uid).remove().length > 0);
    pfc.updateRolesTitles();
    return removed;
  }

  /**
   * Hide or show the roles titles
   */
  pfc.updateRolesTitles = function () {
    [ $(pfc.element).find('div.pfc-role-admin'),
      $(pfc.element).find('div.pfc-role-user') ].forEach(function (item, index) {
      if (item.find('li').length === 0) {
        item.find('.role-title').hide();
      } else {
        item.find('.role-title').show();
      }
    });
  }

  /**
   * Clear the user list
   */
  pfc.clearUserList = function () {
    $(pfc.element).find('li.user').remove();
    pfc.updateRolesTitles();
    return true;
  }

  /**
   * Appends a message to the interface
   */
  pfc.appendMessage = function (msg) {

    // default values
    msg.from      = (msg.type == 'msg') ? msg.sender : (msg.from !== undefined ? msg.from : 'system-message');
    msg.name      = (pfc.users[msg.sender] !== undefined) ? pfc.users[msg.sender].name : msg.name;
    msg.body      = (msg.body !== undefined) ? msg.body : '';
    msg.timestamp = (msg.timestamp !== undefined) ? msg.timestamp : Math.round(new Date().getTime() / 1000);
    msg.date      = new Date(msg.timestamp * 1000).toLocaleTimeString();
    
    // reformat body text
    if (msg.type == 'join') {
      msg.body = msg.name + ' 加入频道';
    } else if (msg.type == 'leave') {
      msg.body = msg.name + ' left the channel' + (msg.body ? ' (' + msg.body + ')' : '');
    }

    var groupmsg_dom = $(pfc.element).find('.pfc-messages .messages-group:last');
    var messages_dom = $(pfc.element).find('.pfc-messages');
    var html         = null;
    if (groupmsg_dom.attr('data-from') != msg.from) {
      html = $('<div class="messages-group" data-stamp="" data-from="">'
//      + '       <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=30" alt="" /></div>'
//      + '       <div class="avatar"><div style="width:30px; height: 30px; background-color: #DDD;"></div></div>'
        + '       <div class="date"></div>'
        + '       <div class="name"></div>'
        + '     </div>');
      
      // system messages (join, error ...)
      if (/^system-/.test(msg.from)) {
        html.addClass('from-' + msg.from);
        html.find('.name').remove();
        html.find('.avatar').remove();
      }
      
      // fill the html fragment
      html.find('.name').text(msg.name);
      html.attr('data-from', msg.from);
      html.find('.date').text(msg.date);
      html.attr('data-stamp', msg.timestamp);
        
      // add a new message group
      messages_dom.append(html);
      groupmsg_dom = html;
    }

    // add the message to the latest active message group
    msg.body = $('<pre></pre>').text(msg.body).html();
    var message = $('<div class="message"></div>').html(msg.body);
    groupmsg_dom.append(message);

    // scroll when a message is received
    if (groupmsg_dom == html) {
      messages_dom.scrollTop(messages_dom.scrollTop() + groupmsg_dom.outerHeight() + 10);
    } else {
      messages_dom.scrollTop(messages_dom.scrollTop() + message.outerHeight());
    }
    
    return message;
  };
  
  /**
   * Setup topic text
   */
  pfc.setTopic = function (topic) {
    $(pfc.element).find('.pfc-topic-value').text(topic);
  };

  /**
   * Shows a popup to ask for help with a donation
   */
  pfc.showDonationPopup = function (next) {
    
    // force skip by a parameter ?
    if (pfc.options.skip_intro) {
      next();
      return;
    }

    // check if skip intro checkbox has been set or not last time
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/skipintro'
    }).complete(function (jqXHR) {
      if (jqXHR.status != 200) {
        buildAndShowDonationPopup();
      } else {
        next();
      }
    });

    function buildAndShowDonationPopup() {
      // html of the popup
      var box = pfc.modalbox.open(
          '<form class="popup-donate">'
        + '  <p>phpFreeChat is an adventure we have been sharing altogether since 2006.'
        + '     If this chat is a so successfull, with hundreds of daily downloads,'
        + '     it is thanks to those who have been helping the project financially.'
        + '     Keep making this adventure possible, make a donation. Thank you.'
        + '  </p>'
        + '  <div clalss="bt-validate">'
        + '    <input type="submit" name="cancel-donate" value="not now" />'
        + '    <input type="submit" name="ok-donate" value="DONATE" />'
        + '  </div>'
        + '  <span><label><input type="checkbox" name="skip-donate" /> skip next time</label></span>'
        + '</form>'
      );
      
      // default focus to donate button
      box.find('input[name=ok-donate]').focus();

      // press ESC to hide donate popup
      var esc_key_action = function (event) {
        if ( event.which == 27 ) {
          pfc.modalbox.close(true);
          $(document).off('keyup', esc_key_action);  // removes escape key event handler
          next();
        }
      };
      $(document).on('keyup', esc_key_action); 
      

      // donate or cancel button clicked
      box.find('input[type=submit]').click(function () {
        // donate button clicked
        if ($(this).attr('name') == 'ok-donate') {
          window.open('http://www.phpfreechat.net/donate', 'pfc-donate'); //,'width=400,height=200');
        }
        // skip intro button clicked
        if (box.find('input[name=skip-donate]').attr('checked')) {
          $.ajax({
            type: pfc.options.use_post_wrapper ? 'POST' : 'PUT',
            url:  pfc.options.serverUrl + '/skipintro',
            data: pfc.options.use_post_wrapper ? { _METHOD: 'PUT' } : 1
          }).done(function (res) {
          }).error(function (err) {
          });
        }
        pfc.modalbox.close(true);
        $(document).off('keyup', esc_key_action); // removes escape key event handler
        next();
      });
      
      // disable submit button action
      box.submit(function (evt) {
        evt.preventDefault();
      });
    }
    
  };

  /**
   * Displays an error
   * first parameter is the err object returned by the AJAX request
   */
  pfc.displayError = function (err) {
    
    // format the error (generic or specific)
    if (err.responseText) {
      err = JSON.parse(err.responseText);
    } else {
      err = {
        'error':      err.statusText,
        'errorCode' : err.status
      };
    }
    
    // display the error
    switch (err.errorCode) {
      
      case 40305:
        err.baninfo.timestamp = new Date(err.baninfo.timestamp * 1000);
        pfc.appendMessage({
          type: 'error',
          body: 'You cannot join this channel because you have been banned by ' + err.baninfo.opname +
                ' for ' + (err.baninfo.reason ? 'the reason "' + err.baninfo.reason + '"' : 'no reason') +
                ' on ' + err.baninfo.timestamp
        });
        break;

      default:
        // generic error
        pfc.appendMessage({
          type: 'error',
          body: err.error + ' [' + err.errorCode + ']'
        });
        break;
    }
  };
 
  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 500, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's init functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  /**
   * phpFreeChat entry point
   */
  pfc.init = function (plugin) {
    // copy jquery attributes needed by phpfreechat
    pfc.element = plugin.element;
    pfc.options = plugin.options;

    // users and channels data (cache)
    pfc.users    = {}; // users of the chat (uid -> userdata)
    pfc.channels = {}; // channels of the chat (cid -> chandata)

    // session data
    pfc.uid = null; // current connected user
    pfc.cid = null; // current active channel

    // check backlink presence
    if (pfc.options.check_backlink && !pfc.hasBacklink()) {
      return;
    }
    
    // load the interface
    pfc.loadHTML();
    pfc.loadResponsiveBehavior();
    
    // run quick tests
    pfc.checkServerConfig(pfc.startChatLogic);
  }

  /**
   * Run few tests to be sure the server is ready to receive requests
   */
  pfc.checkServerConfig = function (next) {
  
    if (pfc.options.check_server_config) {
      pfc.checkServerConfigPHP(function () {
        pfc.checkServerConfigRewrite(next);
      });
    } else {
      next();
    }
    
  };

  /**
   * Test the server php config file
   */
  pfc.checkServerConfigPHP = function (next) {
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverCheckUrl
    }).done(function (errors) {
      // parse json
      try {
        if (errors instanceof String) {
          errors = JSON.parse(errors);
        }
      } catch (err) {
        errors = [ errors ];
      }
      // show errors if one
      if (errors && errors.length > 0) {
        pfc.showErrorsPopup(errors);
      } else {
        next();
      }
    }).error(function () {
      pfc.showErrorsPopup([ 'Unknown error: check.php cannot be found' ]);
    });
  };
      
  /**
   * Test the rewrite rules are enabled on the server
   */
  pfc.checkServerConfigRewrite = function (next) {
    var err_rewrite_msg = 'mod_rewrite must be enabled server side and correctly configured. "RewriteBase" could be adjusted in server/.htaccess file.';
    $.ajax({
      type: 'GET',
      url:  pfc.options.serverUrl + '/status'
    }).done(function (status) {
      if (!status || !status.running) {
        pfc.showErrorsPopup([ err_rewrite_msg ]);
      } else {
        next();
      }
    }).error(function () {
      pfc.showErrorsPopup([ err_rewrite_msg ]);
    });
  };
  
  /**
   * Start to authenticate and to prepare chat dynamic
   */
  pfc.startChatLogic = function () {

    // show donation popup if not skiped
    pfc.showDonationPopup(function () {
      if (!pfc.options.skip_auth) {
        // then try to authenticate
        pfc.login();
      }
    });
    
    // when logged in
    $(pfc.element).bind('pfc-login', function (evt, pfc, userdata) {
      pfc.uid = userdata.id;
      pfc.users[userdata.id] = userdata;
      pfc.cid = 'xxx'; // static channel id for the first 2.x version
      
      if (pfc.options.focus_on_connect) {
        // give focus to input textarea when auth
        $('div.pfc-compose textarea').focus();
      }
      
      // start to read pending messages on the server
      pfc.readPendingMessages(true); // true = loop
      
      // join the default channel
      pfc.join(pfc.cid);
    });

    // when logged out
    $(pfc.element).bind('pfc-logout', function (evt, pfc, userdata) {
      pfc.uid = null;
      pfc.clearUserList();
    });
  };
  
  /**
   * Check backlink in the page
   */
  pfc.hasBacklink = function () {
    var backlink = $('a[href="http://www.phpfreechat.net"]').length;
    if (!backlink) {
      $(pfc.element).html(
        '<div class="pfc-backlink">'
        + '<p>Please insert the phpfreechat backlink somewhere in your HTML in order to load the chat. The attended backlink is:</p>'
        + '<pre>'
        + $('<div/>').text('<a href="http://www.phpfreechat.net">phpFreeChat: simple Web chat</a>').html()
        + '</pre>'
        + '</div>'
      );
      return false;
    }
    return true;
  };

  /**
   * Load HTML used by the interface in the browser DOM
   */
  pfc.loadHTML = function () {
    // load chat HTML
    $(pfc.element).html(
        (pfc.options.loadTestData ?
        '      <div class="pfc-content">'
      : '      <div class="pfc-content pfc-notabs">')
      + '        <div class="pfc-tabs">'
      + '          <ul>'
      + (pfc.options.loadTestData ? ''
      + '            <li class="channel active">'
      + '              <div class="icon"></div>'
      + '              <div class="name">Channel 1</div>'
      + '              <div class="close"></div>'
      + '            </li>'
      + '            <li class="channel">'
      + '              <div class="icon"></div>'
      + '              <div class="name">Channel 2</div>'
      + '              <div class="close"></div>'
      + '            </li>'
      + '            <li class="pm">'
      + '              <div class="icon"></div>'
      + '              <div class="name">admin</div>'
      + '              <div class="close"></div>'
      + '            </li>'
      + '            <li class="new-tab">'
      + '              <div class="icon"></div>'
      + '            </li>'
      : '')
      + '          </ul>'
      + '        </div>'
      + ''
      + '        <div class="pfc-topic">'
      + '          <a class="pfc-toggle-tabs"></a>'
      + '          <p><span class="pfc-topic-label">Topic:</span> <span class="pfc-topic-value">no topic for this channel</span></p>'
      + '          <a class="pfc-toggle-users"></a>'
      + '        </div>'
      + ''
      + '        <div class="pfc-messages">'
      + '          <div class="pfc-message-mobile-padding"></div>' // used to move message at bottom on mobile interface
      + (pfc.options.loadTestData ? ''
      + '          <div class="messages-group" data-stamp="1336815502" data-from="kerphi">'
      + '            <div class="avatar"><img src="http://www.gravatar.com/avatar/ae5979732c49cae7b741294a1d3a8682?d=wavatar&s=30" alt="" /></div>'
      + '            <div class="date">11:38:21</div>'
      + '            <div class="name">kerphi</div>'
      + '            <div class="message">123 <a href="#">test de lien</a></div>'
      + '            <div class="message">456</div>'
      + '          </div>'
      + '          <div class="messages-group" data-stamp="1336815503" data-from="admin">'
      + '            <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=30" alt="" /></div>'
      + '            <div class="date">11:38:22</div>'
      + '            <div class="name">admin</div>'
      + '            <div class="message">Hello</div>'
      + '            <div class="message">World</div>'
      + '            <div class="message">!</div>'
      + '            <div class="message">A very very very very very very very very very very very very very very very very very very very long text</div>'
      + '          </div>'
      : '')
      + '        </div>'
      + ''
      + '        <div class="pfc-users">'
      + '          <div class="pfc-role-admin">'
      + '            <p class="role-title">Admin</p>'
      + '            <ul>'
      + (pfc.options.loadTestData ? ''
      + '              <li class="first">'
      + '                <div class="status st-active"></div>'
      + '                <div class="name">admin</div>'
      + '                <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000001?d=wavatar&s=20" alt="" /></div>'
      + '              </li>'
      : '')
      + '            </ul>'
      + '          </div>'
      + '          <div class="pfc-role-user">'
      + '            <p class="role-title">Users</p>'
      + '            <ul>'
      + (pfc.options.loadTestData ? ''
      + '              <li class="first">'
      + '                <div class="status st-active"></div>'
      + '                <div class="name myself">kerphi</div>'
      + '                <div class="avatar"><img src="http://www.gravatar.com/avatar/ae5979732c49cae7b741294a1d3a8682?d=wavatar&s=20" alt="" /></div>'
      + '              </li>'
      + '              <li>'
      + '                <div class="status st-inactive"></div>'
      + '                <div class="name">Stéphane Gully</div>'
      + '                <div class="avatar"><img src="http://www.gravatar.com/avatar/00000000000000000000000000000002?d=wavatar&s=20" alt="" /></div>'
      + '              </li>'
      : '')
      + '            </ul>'
      + '          </div>'
      + '        </div>'
      + ''
      + '        <div class="pfc-footer">'
      + (pfc.options.show_powered_by ?
        '          <p class="logo"><a href="http://www.links123.cn" class="bt-donate target="_blank">另客首页</a></p>' :
        '')
      + (pfc.options.loadTestData ? ''
      + '          <p class="ping">150ms</p>'
      + '          <ul>'
      + '            <li><div class="logout-btn" title="Logout"></div></li>'
      + '            <li><div class="smiley-btn" title="Not implemented"></div></li>'
      + '            <li><div class="sound-btn" title="Not implemented"></div></li>'
      + '            <li><div class="online-btn"></div></li>'
      + '          </ul>'
      : '')
      + '        </div>'
      + ''
      + '        <div class="pfc-compose">'
      + '          <textarea data-to="channel|xxx"></textarea>'
      + '        </div>'
      + ''
      + '        <div class="pfc-modal-overlay"></div>'
      + '        <div class="pfc-modal-box"></div>'
      + ''
      + '        <div class="pfc-ad-desktop"></div>'
      + '        <div class="pfc-ad-tablet"></div>'
      + '        <div class="pfc-ad-mobile"></div>'
      + '      </div>'
    );

    // load phpfreechat version and hook it to "powered by" title attribute
    if (pfc.options.show_powered_by) {
      $.ajax({
        type: 'GET',
        url:  pfc.options.packageUrl
      }).done(function (p) {
        // some server force text/plain content-type instead of json
        if (typeof p === 'string') {
          try {
            p = JSON.parse(p); // nedd to parse because content-type can be text/plain on specific servers
          } catch (err) {
          }
        }
        if (p.version) {
          $(pfc.element).find('p.logo a.bt-powered').attr('title', 'version ' + p.version);
        }
      });
    }
    
    // connect the textarea enter key event
    $('.pfc-compose textarea').keydown(function (evt) {
      if (evt.keyCode == 13 && evt.shiftKey === false) {
        pfc.postCommand($('.pfc-compose textarea').val());
        $('.pfc-compose textarea').val('');
        return false;
      }
    });

    // when window is resized,
    // resize the textarea with javascript (because absolute positionning doesn't work on firefox)
    $(window).resize(function () {
      var textarea_border_width = parseInt($('.pfc-compose textarea').css('border-right-width'), 10);
      var textarea_padding = parseInt($('.pfc-compose textarea').css('padding-right'), 10)
                           + parseInt($('.pfc-compose textarea').css('padding-left'), 10);
      $('.pfc-compose textarea').width($('.pfc-compose').innerWidth() - textarea_border_width * 2 - textarea_padding);
    });

    // when window is reloaded or closed
    $(window).unload(function () {
      pfc.notifyThatWindowIsClosed();
    });
    
    // once html is loaded init modalbox
    // because modalbox is hooked in pfc's html
    pfc.modalbox.init();

    // call the loaded callback when finished
    if (pfc.options.loaded) {
      pfc.options.loaded(pfc);
    }
    // trigger the pfc-loaded event when finished
    setTimeout(function () { $(pfc.element).trigger('pfc-loaded', [ pfc ]) }, 0);
  };

  /**
   * Function used to display errors list in the overlay popup
   */
  pfc.showErrorsPopup = function (errors) {
    var popup = $('<ul class="pfc-errors"></ul>');
    $.each(errors, function (i, err) {
      popup.append($('<li></li>').html(err));
    });
    pfc.modalbox.open(popup);
  };
  
  /**
   * For mobile ergonomics
   **/
  pfc.loadResponsiveBehavior = function () {
    var elt_tabs     = $(".pfc-tabs");
    var elt_users    = $(".pfc-users");
    var elt_messages = $(".pfc-messages");
    var height_slidetabs = elt_tabs.height();
    var width_users      = elt_users.width();
    var tab_slide_status = 0;
    
    
    // monitor mobile/desktop version
    // and switch tabs css class to adapte styles
    var elt_toggle_tabs_btn  = $('a.pfc-toggle-tabs');
    var elt_toggle_users_btn = $('a.pfc-toggle-users');
    $(window).resize(function () {
      if (elt_toggle_tabs_btn.is(':visible')) {
        switchTabsToMobileLook();
        scrollMessagesToBottom();
      } else {
        switchTabsToDesktopLook();
      }
      if (elt_toggle_users_btn.is(':visible')) {
        switchUsersToMobileLook();
        scrollMessagesToBottom();
      } else {
        switchUsersToDesktopLook();
      }
    });

    
    // tabs mobile version
    function switchTabsToMobileLook() {
      elt_tabs.removeClass('pfc-tabs').addClass('pfc-mobile-tabs');
      elt_tabs.hide();
      if (tab_slide_status == 1) {
        slideTabsUp();
        tab_slide_status = 0;
      }
    }

    // tabs desktop version
    function switchTabsToDesktopLook() {
      elt_tabs.addClass('pfc-tabs').removeClass('pfc-mobile-tabs');
      elt_tabs.show();
      if (tab_slide_status == 1) {
        slideTabsUp();
        tab_slide_status = 0;
      }
    }
    
    // move messages/users up and down if needed
    function slideTabsUp(withtabs) {
      if (withtabs) {
        elt_tabs.slideUp(500);
      }
      elt_messages.animate({
        top: "-=" + height_slidetabs
      }, 500);
      elt_users.animate({
        top: "-=" + height_slidetabs
      }, 500);
    }
    function slideTabsDown(withtabs) {
      if (withtabs) {
        elt_tabs.slideDown(500);
      }
      elt_messages.animate({
        top: "+=" + height_slidetabs
      }, 500);
      elt_users.animate({
        top: "+=" + height_slidetabs
      }, 500);
    }
    
    // show/hide channels tabs
    elt_toggle_tabs_btn.click(function () {
      elt_tabs.removeClass('pfc-tabs').addClass('pfc-mobile-tabs');
      height_slidetabs = elt_tabs.height();
      if (elt_tabs.is(":visible")) {
        tab_slide_status = 0;
        slideTabsUp(true);
      } else {
        tab_slide_status = 1;
        slideTabsDown(true);
      }
    });
    
    // show/hide user list
    elt_toggle_users_btn.click(function () {
      if (elt_users.is(":visible")) {
        elt_users.animate({
          width: "-=" + width_users
        }, 500);
        setTimeout(function () {
          elt_users.hide();
        }, 500);
      } else {
        elt_users.css("width", "0px").show();
        elt_users.animate({
          width: "+=" + width_users
        }, 500);
      }
    });

    // users mobile version
    function switchUsersToMobileLook() {
      elt_users.hide();
    }

    // users desktop version
    function switchUsersToDesktopLook() {
      elt_users.css("width", width_users + "px").show();
    }
    
    // function in charge of scrolling messages list to bottom
    function scrollMessagesToBottom() {
      var messages_dom = $(pfc.element).find('.pfc-messages');
      
      // calculate how many to scroll to have a bottom scrollbar
      var messages_height = 0;
      messages_dom.each(function (i, elt) { messages_height += $(elt).height(); });
      
      messages_dom.scrollTop(messages_dom.scrollTop() + messages_height);
    }
    
  };
  


  return pfc;
}(phpFreeChat || {}, jQuery, window));
/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's JQuery plugin
 * http://www.phpfreechat.net
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  var pluginName = 'phpfreechat';
  var document = window.document;
  var defaults = {
    // phpfreechat server url
    serverUrl: '../server',

    // phpfreechat package.json url
    packageUrl: '../package.json',

    // phpfreechat check.php url
    serverCheckUrl: '../check.php',
    
    // callback executed when interface is loaded
    loaded: null,
    
    // load interface data (only used for tests and design work)
    loadTestData: false,
    
    // time to wait between each message check
    refresh_delay: 5000,
    
     // Setting this to true will give the focus to the input text box when connecting to the chat
    focus_on_connect: true,
    
    // if true a backlink to phpfreechat must be present in the page (see license page for more info)
    check_backlink: true,

    // if true powered by phpfreechat text is shown
    show_powered_by: true,
    
    // set it to true if PUT and DELETE http methods are not allowed by the server
    use_post_wrapper: true,
    
    // when true, the first AJAX request is used to verify that server config is ok
    check_server_config: true,
    
    // number of tolerated network error before stoping chat refresh
    tolerated_network_errors: 5,
    
    // flag used to force skiping intro message about donation
    skip_intro: false,

    // skip login step ? (if true, chat will not be usable)
    skip_auth: false
  };

  function Plugin(element, options) {

    // to be sure options.serverUrl is filled
    options = $.extend({}, options);
    if (!options || !options.serverUrl) {
      options.serverUrl = defaults.serverUrl;
    }
    
    // adjust the packageUrl parameter if serverUrl is specified
    if (!options || !options.packageUrl) {
      options.packageUrl = options.serverUrl + '/../package.json';
    }
    // same for serverCheckUrl
    if (!options || !options.serverCheckUrl) {
      options.serverCheckUrl = options.serverUrl + '/../check.php';
    }
    
    // plugin attributs
    this.element = element;
    this.options = $.extend({}, defaults, options);
    this._defaults = defaults;
    this._name = pluginName;

    // run phpfreechat stuff
    pfc.init(this);
  }

  // connect as a jquery plugin
  // multiple instantiations are forbidden
  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
      }
    });
  }

  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jslint node: true, maxlen: 150, maxerr: 50, indent: 2 */
'use strict';

/**
 * phpfreechat's users related functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  /**
   * Returns the uid from the user's name
   */
  pfc.getUidFromName = function (name) {
    var result = null;
    $.each(pfc.users, function (uid, user) {
      if (name === user.name) {
         result = uid;
      }
    });
    return result;
  };
 
  return pfc;
}(phpFreeChat || {}, jQuery, window));/*jshint jshint:false*/

/**
 * phpfreechat's helper functions
 */
var phpFreeChat = (function (pfc, $, window, undefined) {

  jQuery = $;

  /**
   * MD5 algorithme
   */
  pfc.md5 = function md5(s) {function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,d,F,H,x;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);d=(k&1073741824);x=(G&1073741823)+(k&1073741823);if(I&d){return(x^2147483648^F^H)}if(I|d){if(x&1073741824){return(x^3221225472^F^H)}else{return(x^1073741824^F^H)}}else{return(x^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]|(G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]|(128<<d);aa[I-2]=F<<3;aa[
I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k=k+F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/rn/g,"n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,
1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,
C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=D(Y,X,W,V,C[P+9],o,3654602809);V=D(V,Y,X,W,C[P+12],m,3873151461);W=D(W,V,Y,X,C[P+15],l,530742520);X=D(X,W,V,Y,C[P+2],j,3299628645);Y=t(Y,X,W,V,C[P+0],U,4096336452);V=t(V,Y,X,W,C[P+7],T,1126891415);W=t(W,V,Y,X,C[P+14],R,2878612391);X=t(X,W,V,Y,C[P+5],O,4237533241);Y=t(Y,X,W,V,C[P+12],U,1700485571);V=t(V,Y,X,W,C[P+3],T,2399980690);W=t(W,V,Y,X,C[P+10],R,4293915773);X=t(X,W,V,Y,C[P+1],O,2240044497);Y=t(Y,X,W,V,C[P+8],U,1873313359);V=t(V,Y,X,W,C[P+15],T,4264355552);W=t(W,V,Y,X,C[P+6],R,2734768916);X=t(X,W,V,Y,C[P+13],O,1309151649);Y=t(Y,X,W,V,C[P+4],U,4149444226);V=t(V,Y,X,W,C[P+11],T,3174756917);W=t(W,V,Y,X,C[P+2],R,718787259);X=t(X,W,V,Y,C[P+9],O,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}var i=B(Y)+B(X)+B(W)+B(V);return i.toLowerCase()};
  
  /**
   * Base64 algorithme
   * http://www.webtoolkit.info/javascript-base64.html
   */
  var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="",n,r,i,s,o,u,a,f=0;e=Base64._utf8_encode(e);while(f<e.length)n=e.charCodeAt(f++),r=e.charCodeAt(f++),i=e.charCodeAt(f++),s=n>>2,o=(n&3)<<4|r>>4,u=(r&15)<<2|i>>6,a=i&63,isNaN(r)?u=a=64:isNaN(i)&&(a=64),t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a);return t},decode:function(e){var t="",n,r,i,s,o,u,a,f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length)s=this._keyStr.indexOf(e.charAt(f++)),o=this._keyStr.indexOf(e.charAt(f++)),u=this._keyStr.indexOf(e.charAt(f++)),a=this._keyStr.indexOf(e.charAt(f++)),n=s<<2|o>>4,r=(o&15)<<4|u>>2,i=(u&3)<<6|a,t+=String.fromCharCode(n),u!=64&&(t+=String.fromCharCode(r)),a!=64&&(t+=String.fromCharCode(i));return t=Base64._utf8_decode(t),t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);r<128?t+=String.fromCharCode(r):r>127&&r<2048?(t+=String.
fromCharCode(r>>6|192),t+=String.fromCharCode(r&63|128)):(t+=String.fromCharCode(r>>12|224),t+=String.fromCharCode(r>>6&63|128),t+=String.fromCharCode(r&63|128))}return t},_utf8_decode:function(e){var t="",n=0,r=c1=c2=0;while(n<e.length)r=e.charCodeAt(n),r<128?(t+=String.fromCharCode(r),n++):r>191&&r<224?(c2=e.charCodeAt(n+1),t+=String.fromCharCode((r&31)<<6|c2&63),n+=2):(c2=e.charCodeAt(n+1),c3=e.charCodeAt(n+2),t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63),n+=3);return t}};  
  pfc.base64 = Base64;

  /**
   * PFC's Modal box
   * Todo: make it a jquery plugin
   */
  pfc.modalbox = {
    open: function (html) {
      html = $(html);
      $('div.pfc-modal-box *').remove();
      $('div.pfc-modal-box').append(html).fadeIn();
      if ($.browser.msie) {
        // ie bug: black screen if fadeIn is used (opacity is set to 1 instead of keeping the css fixed value)
        $('div.pfc-modal-overlay').show();
      } else {
        $('div.pfc-modal-overlay').fadeIn('fast');
      }
      $(window).trigger('resize'); // force new width calculation
      return html;
    },
    close: function (now) {
      if (now) {
        $('div.pfc-modal-box').hide();
        $('div.pfc-modal-overlay').hide();
      } else {
        $('div.pfc-modal-box').fadeOut();
        $('div.pfc-modal-overlay').fadeOut('fast');
      }
    },
    init: function () {
      $(window).resize(function setModalBoxPosition() {
        var mb = $('div.pfc-modal-box');
        var mo = $('div.pfc-modal-overlay');
        mb.css({
          left: (mo.outerWidth(true)-mb.outerWidth(true))/2,
          top:  (mo.outerHeight(true)-mb.outerHeight(true))/2
        });
      }).trigger('resize');
    }
  };

  return pfc;
}(phpFreeChat || {}, jQuery, window));