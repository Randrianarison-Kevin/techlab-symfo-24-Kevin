import React from 'react';

const Hello = (props) => {
    console.log("Hello component props:", props);
    return <div>Hello {props.fullName}</div>;
}
export default Hello;
