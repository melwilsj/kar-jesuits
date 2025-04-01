import React from 'react';
import { TouchableOpacity, Text, StyleSheet, Image } from 'react-native';
import Colors, { Color } from '@/constants/Colors';

interface GoogleLoginProps {
  onPress: () => Promise<void>;
}

export default function GoogleLogin({ onPress }: GoogleLoginProps) {
  return (
    <TouchableOpacity style={styles.button} onPress={onPress}>
      <Image
        source={require('@/assets/images/google.png')}
        style={styles.icon}
      />
      <Text style={styles.text}>Continue with Google</Text>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: Color.background,
    padding: 16,
    borderRadius: 8,
    borderWidth: 1,
    borderColor: Color.border,
  },
  icon: {
    width: 24,
    height: 24,
    marginRight: 12,
  },
  text: {
    color: Color.text,
    fontSize: 16,
    fontWeight: '500',
  },
}); 