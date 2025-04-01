import React, { forwardRef } from 'react';
import { StyleSheet, TextInput, View, TouchableOpacity } from 'react-native';
import { MaterialIcons } from '@expo/vector-icons';
import Colors, { Color } from '@/constants/Colors';
import { useColorScheme } from '@/hooks/useSettings';

interface SearchBarProps {
  onFocus?: () => void;
  onChangeText?: (text: string) => void;
  value?: string;
}

const SearchBar = forwardRef<TextInput, SearchBarProps>(({ 
  onFocus, 
  onChangeText, 
  value
}, ref) => {
  const colorScheme = useColorScheme();

  return (
    <View 
      style={[
        styles.searchBar,
        { backgroundColor: Colors[`${colorScheme}`].background }
      ]}
    >
      <MaterialIcons 
        name="search" 
        size={20} 
        color={Colors[`${colorScheme}`].icon    } 
      />
      <TextInput
        ref={ref}
        style={[
          styles.input,
          { color: Colors[`${colorScheme}`].text }
        ]}
        placeholder="Search jesuits, communities..."
        placeholderTextColor={Colors[`${colorScheme}`].textSecondary}
        onFocus={onFocus}
        onChangeText={onChangeText}
        value={value}
        returnKeyType="search"
        autoCapitalize="none"
        autoCorrect={false}
      />
      {value && value.length > 0 && (
        <TouchableOpacity 
          onPress={() => onChangeText && onChangeText('')}
          hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
        >
          <MaterialIcons
            name="close"
            size={20}
            color={Colors[`${colorScheme}`].textSecondary}
          />
        </TouchableOpacity>
      )}
    </View>
  );
});

export default SearchBar;

const styles = StyleSheet.create({
  searchBar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 8,
    height: 40,
    flex: 1,
  },
  input: {
    flex: 1,
    marginLeft: 8,
    fontSize: 16,
  },
}); 