import styled from 'styled-components';

export const ServiceList = styled.ul`
  box-sizing: border-box;
  list-style: none;
  margin: 0;
  padding: 1rem 0;
  display: inline-flex;
  border: solid 3px #eee;
  border-radius: 6px;

  li {
    flex: 0 0 auto;
    width: 250px;
    display: flex;
    flex-direction: row;
    margin: 0;
    padding: 0 1rem 0;
    box-sizing: border-box;

    &:not(:first-child) {
      border-left: solid 1px #eee;
    }
  }

  input[type="radio"] {
    margin-top: 3px;
    margin-right: 1rem;
  }

  p {
    margin-top: 0;
    margin-bottom: 5px;
  }
`;
