const eventId = window.location.pathname.split('/').pop();
const eventSource = new EventSource(
    `http://127.0.0.1:3000/.well-known/mercure?topic=event/${eventId}`,
    {withCredentials: true}
);

eventSource.onmessage = (event) => {
    console.log("comment added");
};

// const eventId = window.location.pathname.split('/').pop();
// const url = new URL(window.location.href);
// fetch(url.origin + `/mercure/subscribe/${eventId}`, { credentials: 'include' })
//     .then(response => response.json())
//     .then(data => {
//         const jwt = data.jwt;
//         const eventSource = new EventSource(
//             `http://127.0.0.1:3000/.well-known/mercure?topic=event/${eventId}&jwt=${jwt}`
//         );

//         eventSource.onmessage = (event) => {
//             console.log("Comment added");
//         };
//     })
//     .catch(console.error);
