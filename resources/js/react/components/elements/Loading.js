import React from 'react';
import styled, { keyframes } from 'styled-components';

const hourglass = keyframes`
  0% {
    transform: rotate(0);
    animation-timing-function: cubic-bezier(0.55, 0.055, 0.675, 0.19);
  }
  50% {
    transform: rotate(900deg);
    animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1);
  }
  100% {
    transform: rotate(1800deg);
  }
`;

const Hourglass = styled.div`
  display: inline-block;
  position: relative;
  width: 80px;
  height: 80px;

  &:after {
    content: " ";
    display: block;
    border-radius: 50%;
    width: 0;
    height: 0;
    margin: 8px;
    box-sizing: border-box;
    border: 32px solid #bcd4cc;
    border-color: #bcd4cc transparent #bcd4cc transparent;
    animation: ${hourglass} 1.2s infinite;
  }
`;

const Container = styled.div`
  display: flex;
  justify-content: center;
  padding: 1rem;
`;

function Loading({ isLoading = false }) {
  if (isLoading === false) {
    return null;
  }

  return (
    <Container>
      <Hourglass />
    </Container>
  );
}

export default Loading;
