import React from 'react';
import PropTypes from 'prop-types';

const { publicPath } = window.vnShippingSettings;

function CourierItem({ courier, isChecked, onChange }) {
  const { id, name, icon } = courier;

  return (
    <li>
      <input
        type="radio"
        name="courier"
        value={id}
        checked={isChecked || false}
        onChange={() => onChange(courier)}
        id={`choose_${id}`}
      />
      <label htmlFor={`choose_${id}`}>
        {icon ? <img src={`${publicPath}/${icon}`} alt={name} /> : null}
        <strong>{name}</strong>
      </label>
    </li>
  );
}

CourierItem.propTypes = {
  onChange: PropTypes.func.isRequired,
  isChecked: PropTypes.bool.isRequired,
  courier: PropTypes.shape({
    id: PropTypes.string.isRequired,
    name: PropTypes.string.isRequired
  }).isRequired
};

export default CourierItem;
