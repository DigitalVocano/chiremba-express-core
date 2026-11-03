!function(w,h,a,t,s,p){
    w.whatsapp || (
    s = h.createElement(a),
    s.src = t,
    p = h.getElementsByTagName(a)[0],
    p.parentNode.insertBefore(s, p)
)}(window, document, "script", "js/whatsappjs.js");
window.addEventListener('load', function(){
    new whatsapp({
        agents:[{
            name: "Support",
            phone: "+263717514013",
            hours: "From 9am - 6pm",
            cta: "KumbaCare Support"
        },
        {
            name: "Medical Insurance",
            phone: "+263711312395?text=REF00105",
            hours: "24/7",
            cta: "Onehealth"
        }]
    })
});
