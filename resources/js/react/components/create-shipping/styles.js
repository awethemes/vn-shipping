import styled from 'styled-components';

export const CourierList = styled.ul`
  box-sizing: border-box;
  list-style: none;
  display: flex;
  justify-content: center;
  align-content: center;
  flex-direction: row;
  flex-wrap: wrap;
  margin: 0 auto;
  padding: 0;

  li {
    box-sizing: border-box;
    cursor: pointer;

    margin: 0 0.5rem 0.5rem;
    width: 180px;
    max-width: 180px;
    position: relative;
    z-index: 10;
  }

  input {
    position: absolute;
    z-index: 11;
    top: 0.7rem;
    left: 0.5rem;
  }

  label {
    display: flex;
    align-items: center;
    flex-direction: column;
    padding: 1rem;

    border: solid 3px #eee;
    border-radius: 4px;

    &:hover,
    &:active {
      border-color: #ddd;
    }
  }

  input:checked ~ label {
    border-color: #607d8b;
  }

  img {
    width: auto;
    max-width: 100%;
    height: 42px;
    object-fit: contain;
  }

  strong {
    display: block;
    margin-top: 0.5rem;
  }
`;

export const Actions = styled.div`
  display: flex;
  justify-content: center;
  margin-top: 1rem;
`;
