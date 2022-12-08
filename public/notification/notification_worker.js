const title = "Z&L Boutik";
const msg = "";
const icon = "/site/img/footer-logo-no.png";
const song = "/notification/sound.wav";

const global_socket_url = "https://stimulus.mutuelleawoundjo.com"

async function callNotification(title, msg, icone) {

    new Notification(title, {
        body: msg,  
        icon: icone,
        vibrate: true
    });

    const audio = new Audio(song);
    
    audio.play();

}

function notify(msg) {
    
    if (!("Notification" in window)) {
        //alert("This browser does not support Desktop notifications");
    }
    if (Notification.permission === "granted") {
        callNotification(title, msg, icon);
        return;
    }else if(Notification.permission !== "denied") {
        Notification.requestPermission().then(permission => {
        if (permission === "granted") {
            callNotification(title, msg, icon);
        }
        });
        return;
    }
}

const global_socket = io(global_socket_url)

global_socket.on('zlboutik-commande', (content) => {

    //const data = JSON.parse(content.data)

    console.log(content.data);

    notify(`Nouvelle commande. Ref: `);


});