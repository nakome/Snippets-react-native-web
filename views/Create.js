import { useNavigate } from "react-router-native";
import {
  Text,
  View,
  Button,
  Picker,
  Switch,
  TextInput,
  StyleSheet,
  SafeAreaView,
  ScrollView,
  ActivityIndicator,
  Modal,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useEffect, useState, useContext } from "react";

// context
import ThemeOptions from "../services/ThemeOptions";
// theme options
import { ThemeStyleColors } from "../config/Theme";
// categories
import Categories from "../config/Categories";
// services
// services
import { insertDb } from "../services/Store";
// utils
import { encodeUnicode } from "../utils/base64";

export default function Create() {
  const navigate = useNavigate();

  const config = useContext(ThemeOptions);
  const [loaded, setLoaded] = useState(false);
  // set values
  const [title, setTitle] = useState("");
  const [description, setDescription] = useState("");
  const [category, setCategory] = useState("Bash");
  const [content, setContent] = useState("");
  // modal
  const [isVisible, setIsVisible] = useState(false);
  const [msg, setMsg] = useState("");

  // switch
  const [isEnabled, setIsEnabled] = useState(false);
  const toggleSwitch = () => setIsEnabled((previousState) => !previousState);

  const handleBtnSave = async () => {
    // array of values
    let arr = {
      title: title,
      description: description,
      category: category,
      public: isEnabled ? 1 : 0,
      content: JSON.stringify({
        snippet: encodeUnicode(content),
      }),
      author: config.data.author,
      author_info: config.data.authorInfo,
    };

    // check if is not empty
    if (title && description && category && content) {
      setLoaded(false);
      try {
        const response = await insertDb("snippets", arr);
        setIsVisible(true);
        setMsg(response);
      } catch (e) {
        console.log(e);
      } finally {
        setLoaded(true);
      }
    }
  };

  useEffect(() => setLoaded(true), []);

  if (!loaded) {
    return (
      <View style={[styles.container, styles.horizontal]}>
        <ActivityIndicator size="large" color={ThemeStyleColors.warning} />
      </View>
    );
  }

  return (
    loaded && (
      <SafeAreaView style={styles.container}>
        <Modal
          transparent
          animationType={"fade"}
          onRequestClose={() => setIsVisible(false)}
          visible={isVisible}
        >
          <View style={styles.modal}>
            <Text style={styles.modalTitle}>{msg}</Text>
            <View style={styles.buttons}>
              <Button
                color={ThemeStyleColors.primary}
                title={[
                  <Ionicons
                    name="arrow-back-circle-outline"
                    size={16}
                    color={ThemeStyleColors.warning}
                    style={{ marginRight: 5 }}
                  />,
                  config.lang.backToHome,
                ]}
                onPress={() => navigate("/")}
              />
              <Button
                color={ThemeStyleColors.danger}
                title={[
                  <Ionicons
                    name="close-circle-outline"
                    size={16}
                    color={ThemeStyleColors.warning}
                    style={{ marginRight: 5 }}
                  />,
                  config.lang.cancel,
                ]}
                onPress={() => setIsVisible(false)}
              />
            </View>
          </View>
        </Modal>
        <ScrollView>
          <Text style={styles.label}>{config.lang.labelName}</Text>
          <TextInput
            maxLength={40}
            style={styles.input}
            onChangeText={(txt) => setTitle(txt)}
            value={title}
          />
          <Text style={styles.label}>{config.lang.description}</Text>
          <TextInput
            maxLength={100}
            style={styles.input}
            onChangeText={(txt) => setDescription(txt)}
            value={description}
          />

          <Text style={styles.label}>{config.lang.selectCategory}</Text>
          <Picker
            selectedValue={category}
            style={styles.picker}
            onValueChange={(itemValue, itemIndex) => setCategory(itemValue)}
          >
            {Categories.map((item, index) => (
              <Picker.Item
                key={`id_${index.toString()}`}
                label={item}
                value={item}
              />
            ))}
          </Picker>

          <Text style={styles.label}>{config.lang.whiteCode}</Text>
          <TextInput
            multiline
            numberOfLines={10}
            onChangeText={(txt) => setContent(txt)}
            value={content}
            style={styles.content}
          />

          <View style={styles.switchContainer}>
            <Switch
              style={styles.switch}
              trackColor={{
                false: ThemeStyleColors.primary,
                true: ThemeStyleColors.primary,
              }}
              thumbColor={
                isEnabled ? ThemeStyleColors.info : ThemeStyleColors.danger
              }
              ios_backgroundColor={ThemeStyleColors.primary}
              onValueChange={toggleSwitch}
              value={isEnabled}
            />
            <Text style={styles.switchTxt}>{config.lang.publicCode}</Text>
          </View>

          <View style={styles.buttons}>
            <Button
              color={ThemeStyleColors.primary}
              title={config.lang.save}
              onPress={handleBtnSave}
            />
          </View>
        </ScrollView>
      </SafeAreaView>
    )
  );
}

const styles = StyleSheet.create({
  horizontal: {
    flexDirection: "row",
    justifyContent: "space-around",
    padding: 10,
  },
  container: {
    display: "flex",
    flexDirection: "column",
    flex: 1,
    width: "100%",
    maxWidth: 768,
  },
  input: {
    height: 40,
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 10,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  picker: {
    height: 40,
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 5,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  content: {
    marginLeft: 12,
    marginRight: 12,
    marginBottom: 12,
    borderWidth: 1,
    padding: 10,
    backgroundColor: ThemeStyleColors.primary,
    borderColor: ThemeStyleColors.info,
    color: ThemeStyleColors.info,
  },
  switchContainer: {
    display: "flex",
    flexDirection: "row",
  },
  switchTxt: {
    marginTop: 10,
    color: ThemeStyleColors.info,
  },
  switch: {
    margin: 12,
  },
  buttons: {
    margin: 12,
    display: "flex",
    flexDirection: "row",
    justifyContent: "space-between",
    maxWidth: 150,
  },
  label: {
    marginLeft: 12,
    marginRight: 12,
    marginTop: 12,
    marginBottom: 5,
    color: ThemeStyleColors.warning,
  },
  modal: {
    backgroundColor: ThemeStyleColors.secondary,
    borderColor: ThemeStyleColors.primary,
    borderRadius: 4,
    borderWidth: 1,
    padding: 10,
  },
  modalTitle: {
    fontSize: 18,
    margin: 12,
    color: ThemeStyleColors.warning,
  },
});
