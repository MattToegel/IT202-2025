// This section is just to generate a lot of text without hard-coding it
// Ignore this whole script tag, it's not relevant for the challenge
// Don't make any edits/changes here
let content = document.querySelector(".content");
if (content) {
    let p = content.querySelector("p");
    if (p) {
        for (let i = 0; i < 20; i++) {
            let another = p.cloneNode(true);
            content.appendChild(another);
        }
    }
}
let header = document.querySelector("header");
header.innerText += " " + new Date().toLocaleString("en-US");
