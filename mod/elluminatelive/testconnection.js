// $Id: testconnection.js,v 1.2.2.2 2009/10/22 14:28:24 jfilip Exp $

function testConnection(obj, wwwroot) {
/// This function will open a popup window to test the server paramters for
/// successful connection.

    if ((obj.s__elluminatelive_server.value.length == 0) || (obj.s__elluminatelive_server.value == '')) {
        return false;
    }

    var queryString = "";

    queryString += "serverURL=" + escape(obj.s__elluminatelive_server.value);
    queryString += "&adapter=" + obj.s__elluminatelive_adapter.value;
    queryString += "&authUsername=" + escape(obj.s__elluminatelive_auth_username.value);
    queryString += "&authPassword=" + escape(obj.s__elluminatelive_auth_password.value);

    return window.open(wwwroot + '/mod/elluminatelive/conntest.php?' + queryString, 'connectiontest', 'scrollbars=yes,resizable=no,width=640,height=300');
}
