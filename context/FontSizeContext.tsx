import React, { createContext, useContext } from 'react';

const FontSizeContext = createContext(1);

export const FontSizeProvider = FontSizeContext.Provider;

export function useAppFontSize() {
  return useContext(FontSizeContext);
} 